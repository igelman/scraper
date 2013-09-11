<?php

class FileDownloader {
	
	public function __construct($urls) {
		$this->urls = $urls;
	}
	
	public function cleanUrlArray($urlArray) {
		return array_values(array_unique($urlArray)); // de-dupe the array
		// nned to add a check to make sure these are all proper urls, too
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
	
	
}

?>