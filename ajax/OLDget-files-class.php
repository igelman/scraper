<?php

class FileDownloader 
{
	private $urls = array(); // list of urls to download
	private $defaltCurlOptions = array();
	private $curlHandlers = array();
	private $mh; // curl multihandler
	
	public function __construct($urls) {
		$this->urls = cleanUrlArray($urls);
	}
	
	public function getUrls() {
		return $this->urls;
	}

	private function cleanUrlArray($urlArray) {
		$urlArray = array_values(array_unique($urlArray)); // de-dupe the array
		// add a check to make sure these are all valid urls
		if (!$urlArray) die('No URL to check');
		return $urlArray;
	}

	private function createCurlOptionsArray() {
		$userAgent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)";
		$this->defaultCurlOptions = array(
			CURLOPT_RETURNTRANSFER => TRUE, // return content
			CURLOPT_USERAGENT      => $userAgent, // set user-agent
			CURLOPT_AUTOREFERER    => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE, // follow redirects
			CURLOPT_URL            => "", // this is where we'll put the target url
		);
	} // createCurlOptionsArray

	private function createCurlMultiHandler() {
	/**
	Input:
		$urls array of urls
		$defaultOptions default set of curl options
	Output:
		$mh curl multi-handler
		$ch[] array of curl handlers
	**/
		$i = 0;
		$this->mh = curl_multi_init();
		foreach ($this->urls as $url) {
			$options = $this->defaultCurlOptions;
			$options[CURLOPT_URL] = $url; // add the url to curl options (other options are constant) 
		
			// set the handler for ths url			
			$this->curlHandlers[$i] = curl_init();
			curl_setopt_array($this->curlHandlers[$i], $options);
			
			// add the handle to the multi-handler
			curl_multi_add_handle($this->mh, $this->curlHandlers[$i]);
			$i++;
		}
	} // createCurlMultiHandler

	private function executeMultiHandler(){
		$running = null;
		do {
			curl_multi_exec($this->mh, $running);
		} while($running > 0);
	}
	
	private function storeFiles(){
		foreach($this->curlHandlers as $ch){
			handleCurlOutput($ch);
			curl_multi_remove_handle($this->mh, $ch);
		}
	}
	
	private function handleCurlOutput($ch){
		/**
		Input:
			$ch current curl handler
		Side effect:
			Write curl output to local file
			Handle error if no output
		Output:
			$result of current curl handler
		**/
		
			$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			$urlBasename = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_BASENAME );
			
			$fileStore = $GLOBALS['appRootPath'] . $GLOBALS['fileStorePath'] . time() . "-" . $urlBasename ;			
			if ( ($html = curl_multi_getcontent($ch) ) === FALSE){ // check for empty output
				$error = curl_error($ch);
			}
			if ( ($length = file_put_contents($fileStore, $html) ) === FALSE) {
				return "crap";
			}
	}
	
}

$debug = "";
include_once("../config/scraper.config");
include_once("../config/scraper-pages.config"); // $urls is initialized in the config


$fd = new FileDownloader($urls);
echo "<pre>" . print_r($fd->getUrls(), TRUE) . "</pre>";
?>