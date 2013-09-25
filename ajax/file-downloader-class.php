<?php

require_once("pdo-manager-class.php");
require_once("file-parser-class.php");

class FileDownloader {
	private $urls;
	private $fileStores;
	public $mh;
	public $curlHandlers;
	private $appRootPath;
	private $fileStorePath;
	private $sleep = 10;
	
	//private $userAgent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
	public $curlOptsArray = array(
			CURLOPT_RETURNTRANSFER => TRUE, // return content
			CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)", // set user-agent
			CURLOPT_AUTOREFERER    => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE, // follow redirects
			//CURLOPT_URL            => $url, // this is where we'll put the target url
	);
	
	public function __construct($urls) {
		$this->urls = $this->cleanUrlArray($urls);
	}
	
	public function getUrls(){
		return $this->urls;
	}
	
	public function setAppRootPath($appRootPath) {
		$this->appRootPath = $appRootPath;
	}

	public function setFileStorePath($fileStorePath) {
		$this->fileStorePath = $fileStorePath;
	}
	
	public function setExtraCurlOptions($extraOptions) {
		$this->curlOptsArray += $extraOptions;
	}
	
	private function cleanUrlArray($urlArray) {
		return array_values(array_unique($urlArray)); // de-dupe the array
		// nned to add a check to make sure these are all proper urls, too
	}

	private function createCurlOptionsArray($url) {
	/**
	Create array of curl options based on set of defaults and the url parameter
	Input:
		$url
	Output:
		Array of curl options
	**/
		return $this->curlOptsArray + array(CURLOPT_URL=>$url);
	
	} // createCurlOptionsArray
	
	public function createCurlMultiHandler() {
		$this->mh = curl_multi_init();
		foreach ($this->urls as $i => $url) {
			$this->curlHandlers[$i] = curl_init();
			curl_setopt_array($this->curlHandlers[$i], $this->createCurlOptionsArray($url));
			curl_multi_add_handle($this->mh, $this->curlHandlers[$i]);
		}
	} // createCurlMultiHandler
	
	private function executeMultiHandler(){
		$running = null;
		do {
			curl_multi_exec($this->mh, $running);
		} while($running > 0);
	}
	
	public function storeFiles() {
		$this->executeMultiHandler();
		foreach($this->curlHandlers as $ch) {
			$this->stopIfDetected($ch);
			$this->handleCurlOutput($ch);
			curl_multi_remove_handle($this->mh, $ch);
			sleep( rand( 0, $this->sleep));
		}
	}
	
	protected function stopIfDetected($ch){
		$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		if (strstr($effectiveUrl, "humanCheck") ) {
			exit("We got found out: $effectiveUrl");
		}
	}
		
	protected function handleCurlOutput($ch) {
		if ( ($html = curl_multi_getcontent($ch) ) === FALSE){ // check for empty output
		// test length of retrieved file
			$error = curl_error($ch);
		}
		$this->writeCurlToFile($ch, $html);
	}
	
	private function writeCurlToFile($ch, $html) {
		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$urlBasename = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_BASENAME );
		$fileStore = $this->appRootPath . $this->fileStorePath . time() . "-" . $urlBasename ;
		
		
	//	echo PHP_EOL . "*** writeFile ***" . PHP_EOL . "fileStore: $fileStore" . PHP_EOL . "html: " . substr($html, 0, 10) . PHP_EOL . "about to file_put_contents..." . PHP_EOL;
		
		$length = $this->writeFile($fileStore, $html);
		if ( ($length) === FALSE) {
		// test length of written file
			echo "crap" . PHP_EOL;
		}
		
		$this->fileStores[] = array(
			'url'		=>	$url,
			'curlInfo'	=>	curl_getinfo($ch),
			'fileStore'	=>	$fileStore,
		);
	}
	
	public function writeFile($fileStore, $string) {
		return file_put_contents($fileStore, $string);
	}
	
	public function getFileStores() {
		return $this->fileStores;
	}
	
	
}

class ProxyFileDownloader extends FileDownloader {
	
	public function __construct($urls, $proxyIp) {
		$extraOptions = array(
			CURLOPT_PROXY => $proxyIp,
			CURLOPT_HTTPPROXYTUNNEL => TRUE,
			CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
		);
		$this->setExtraCurlOptions($extraOptions);
		parent::__construct($urls);
	}
}

class FileDownloaderWithCallback extends FileDownloader{

	private $callback;
	public function __construct($urls, $callback) {
		$this->callback = $callback;
		parent::__construct($urls);
	}
	
	protected function handleCurlOutput($ch) {
		parent::handleCurlOutput($ch);
//		echo "returned from parent::handleCurlOutput" . PHP_EOL;
		$callback = $this->callback;
		echo PHP_EOL . "hco callback: " . $callback("callback function returned") . PHP_EOL;
		
		$return = $callback("callback function returned");
		echo "hco: $return" . PHP_EOL;
		return $return;
	}
}

class FileToBlobDownloader extends FileDownloader {

	protected function handleCurlOutput($ch) {
		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
		
		if ( ($html = curl_multi_getcontent($ch) ) === FALSE){ // check for empty output
		// test length of retrieved file
			$error = curl_error($ch);
		}
		
		$parsed_content = $this->parseContent($html);
		var_dump($parsed_content);
		$length = $this->writeFile($url, $html, $downloadSize, $parsed_content);
		if ( ($length) === FALSE) {
		// test length of written file
			echo "crap" . PHP_EOL;
		}
	}
	
	public function writeFile($url, $html, $downloadSize, $parsed_content="") {
		echo "F2BD writeFile($url, html)" . PHP_EOL;
		$dbh = PdoManager::getInstance();
		try {
			$stmt = $dbh->prepare("UPDATE files SET content=:html, date_retrieved = NOW(), parsed_content = :parsed_content WHERE url=:url");
			$stmt->bindParam(':html', $html);
			$stmt->bindParam(':url', $url);
			$stmt->bindParam(':parsed_content', $parsed_content);
			
			if($stmt->execute()) {
				echo "Update $url with blob size $downloadSize" . PHP_EOL;
			} else {
				echo "Didn't update $url with blob" . PHP_EOL;
				echo "UPDATE files SET content=html WHERE url=$url" . PHP_EOL . PHP_EOL;
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}		

	}
	
	private function parseContent($html) {
		$rmnParser = FileParser::createFromHtml("RetailmenotParser", $html);
		$rmnParser->parseDomObject();
		return json_encode($rmnParser->getParsedContent());

	}
}

?>