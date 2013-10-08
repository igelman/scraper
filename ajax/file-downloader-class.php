<?php

require_once("pdo-manager-class.php");
require_once("file-parser-class.php");

class FileDownloader {
	private $urls;
	private $curlHandlers;
	private $callback;
	private $callbackReturn;
	protected $sleep;
	
	public $curlOptsArray = array(
			CURLOPT_RETURNTRANSFER => TRUE, // return content
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.47 Safari/537.36", //"Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)", // set user-agent
			CURLOPT_AUTOREFERER    => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE, // follow redirects
			//CURLOPT_URL            => $url, // this is where we'll put the target url
	);
	
	public function __construct($urls, $sleep=2) {
		$this->urls = $this->cleanUrlArray($urls);
		$this->sleep = $sleep;
		// Also, throw an exception if $urls is empty
	}
	
	public function getUrls(){
		return $this->urls;
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
	
	public function getCurlHandlers() {
		return $this->curlHandlers;
	}

	public function createCurlMultiHandler() {
		foreach ($this->urls as $i => $url) {
			$this->curlHandlers[$i] = curl_init();
			curl_setopt_array($this->curlHandlers[$i], $this->createCurlOptionsArray($url));
		}
	} // createCurlMultiHandler
	
	public function executeCurls($callback=null) {
		$callbackReturn = array();
		foreach($this->curlHandlers as $ch) {
			curl_exec($ch);
			$this->stopIfDetected($ch);
			$html = $this->handleCurlOutput($ch);
			if (isset($callback)) {
				//echo "Callback on " . curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
				$callbackReturn[] = $callback($ch, $html); ///////
				$this->callbackExecuted = TRUE;
			}
			//curl_close($ch);
			sleep( rand( ($this->sleep)/2, ($this->sleep))*3/2);
		}
		$this->callbackReturn = $callbackReturn;
	}
	
	public function getCallbackReturn() {
		return $this->callbackReturn;
	}
	
	protected function stopIfDetected($ch){
		$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		if (strstr($effectiveUrl, "humanCheck") ) {
			exit("We got found out: $effectiveUrl" . PHP_EOL);
		}
	}
	
	protected function handleCurlOutput($ch) {
		if ( ($html = curl_exec($ch) ) === FALSE){ // check for empty output
		// test length of retrieved file
			$error = curl_error($ch);
		}
		return $html;
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

?>