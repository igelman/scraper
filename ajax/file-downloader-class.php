<?php
//include_once("../config/scraper.config");
//include_once("../config/scraper-pages.config"); // $urls is initialized in the config

class FileDownloader {
	private $urls;
	private $fileStores;
	public $mh;
	public $curlHandlers;
	private $appRootPath;
	private $fileStorePath;
	
	
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
		$userAgent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)";
		return array(
			CURLOPT_RETURNTRANSFER => TRUE, // return content
			CURLOPT_USERAGENT      => $userAgent, // set user-agent
			CURLOPT_AUTOREFERER    => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE, // follow redirects
			CURLOPT_URL            => $url, // this is where we'll put the target url
		);
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
			$this->handleCurlOutput($ch);
			curl_multi_remove_handle($this->mh, $ch);
		}
	}
	
	private function handleCurlOutput($ch) {
		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$urlBasename = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_BASENAME );
		
		$fileStore = $this->appRootPath . $this->fileStorePath . time() . "-" . $urlBasename ;
//		echo "handleCurlOutput" . PHP_EOL;
//		echo "fileStore: " . $fileStore . PHP_EOL;
		if ( ($html = curl_multi_getcontent($ch) ) === FALSE){ // check for empty output
		// test length of retrieved file
			$error = curl_error($ch);
		}
		$length = $this->writeFile($fileStore, $html);
		if ( ($length) === FALSE) {
		// test length of written file
			echo "crap" . PHP_EOL;
		}
		
		$this->fileStores[] = array(
			'url'		=>	$url,
			'fileStore'	=>	$fileStore,
		);
	}
	
	public function writeFile($fileStore, $html) {
	/**
	This method might be replaced by a DB insert.
	Input:
		$fileStore path to new file location
		$html string of html
	Side Effect:
		Writes file to location $fileStore
	Output:
		Length of written file
	**/
		return file_put_contents($fileStore, $html);
	}
	
	public function getFileStores() {
		return $this->fileStores;
	}
	
	
}

?>