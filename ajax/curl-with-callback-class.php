<?php

class CurlWithCallback {
	private $url;
	private $callback;
	private $curlOptsArray;
	private $ch;
	private $result;
	private $error;
	
	public function __construct($url, $callback = NULL) {
		$this->url = $url;
		$this->callback = $callback;
		$this->curlOptionsArray = array(
			CURLOPT_RETURNTRANSFER => TRUE, // return content
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.47 Safari/537.36", //"Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)", // set user-agent
			CURLOPT_AUTOREFERER    => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE, // follow redirects
			CURLOPT_URL            => $this->url,
		);
		$this->ch = curl_init();
		curl_setopt_array($this->ch, $this->curlOptionsArray);
	}
	
	public function executeCurl() {
		if ( ($this->result = curl_exec($this->ch) ) === FALSE){ // check for empty output
		// test length of retrieved file
			$this->error = curl_error($this->ch);
		}
		if (isset($this->callback)) {
			//echo "Callback on " . curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL) . PHP_EOL;
			$callbackFunction = $this->callback;
			$this->callbackReturn = $callbackFunction($this->ch, $this->result);
		}
	}
	
	public function getCallbackReturn() {
		if (is_null($this->callback)) {
			return NULL;
		}
		return $this->callbackReturn;
	}
	
	public function getCurlResult() {
		return $this->result;
	}
	
	public function getCurlError() {
		return $this->error;
	}

}

?>