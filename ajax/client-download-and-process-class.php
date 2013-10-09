<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("../config/local.config");
require_once("pdo-manager-class.php");
require_once("file-parser-class.php");
require_once("file-downloader-class.php");


class ClientDownloadAndProcess {
	protected $setNumber;
	protected $urls;
	protected $ajaxReturn;
	
	protected function __construct($sleep=null) {
		$this->sleep = $sleep;
	}

	public function getUrls() {
		return $this->urls;
	}
	
	public function processUrls() {
		$callback = $this->createCallbackFunctionToUpdateDb();
		$fd = new FileDownloader($this->urls, $this->sleep);
		$fd->createCurlMultiHandler();
		$fd->executeCurls($callback);
		return $fd->getCallbackReturn();
	}
	
	private function createCallbackFunctionToUpdateDb(){
		return function($ch, $html){
			$return['message'] = "";
			$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			$downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
			if ( ($html ) === FALSE){
				$error = curl_error($ch);
			}
			$dateRetrieved = date("Y-m-d H:i:s");

			$return['message'] .= PHP_EOL . "processUrls downloaded $url ..." . PHP_EOL;

			$rmnParser = FileParser::createFromHtml("RetailmenotParser", $html);
			$rmnParser->parseDomObject();
			$parsed_content = json_encode($rmnParser->getParsedContent());
			$dbh = PdoManager::getInstance();
			try {
				$stmt = $dbh->prepare("UPDATE files SET content=:html, date_retrieved = :dateRetrieved, parsed_content = :parsed_content WHERE url=:url");
				$stmt->bindParam(':html', $html);
				$stmt->bindParam(':dateRetrieved', $dateRetrieved);
				$stmt->bindParam(':url', $url); // no good if there's a redirect. Use the original url, not the curl result.
				$stmt->bindParam(':parsed_content', $parsed_content);
				
				if($stmt->execute()) {
					$return['CURLINFO_EFFECTIVE_URL'] = $url;
					$return['size'] = $downloadSize;
					$return['time'] = $dateRetrieved;
					$return['message'] .= "Update $url with blob size $downloadSize" . PHP_EOL;
					//$return['message'] .= print_r(curl_getinfo($ch), TRUE);
				} else {
					$return['message'] .= "Didn't update $url with blob" . PHP_EOL;
					$return['message'] .= "UPDATE files SET content=html WHERE url=$url" . PHP_EOL . PHP_EOL;
				}
			}
			catch(PDOException $e){
				$return['message'] .= $e->getMessage();
			}
			return $return;
		};
	}
}

class ClientDownloadAndProcessSet extends ClientDownloadAndProcess {

	public function __construct($setNumber, $sleep=null) {
		$this->setNumber = $setNumber;
		$this->selectUrls();
		$this->ajaxReturn['message'] = PHP_EOL . "set construct: setNumber: $setNumber | this->setNumber: $this->setNumber";
		parent::__construct($sleep);
	}


	private function selectUrls() {
		$pm = PdoManager::getInstance();
		try {
			$stmt = $pm->prepare("SELECT url FROM files WHERE set_number = :set_number");
			$stmt->bindParam(':set_number', $this->setNumber);
		
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->execute();
			
			$this->urls = array();
			foreach($stmt as $row) {
				$this->urls[] = $row['url'];
			}
		} catch(PDOException $e) {
			$this->ajaxReturn['message'] .= $e->getMessage();
		}
	}
	
}

class ClientDownloadAndProcessUrls extends ClientDownloadAndProcess {

	public function __construct($urls, $sleep=null) {
		$this->urls = $urls;
		$this->ajaxReturn['message'] = PHP_EOL . "I'm the ClietnDownloadAndProcessUrls class!! url construct: urls: " . print_r($this->urls, TRUE) . PHP_EOL;
		parent::__construct($sleep);
	}
	
	private function testUrls(){
		// make sure urls are in our list else we'll curl them but callback will fail. Probably harmless.
	}

}


/**
* Usage: "php client-class.php <int setNumber>"
*/
/*
$setNumber = (isset($argv[1]) && $argc==2 ) ? $argv[1] : die("Usage: 'php client-class.php <int setNumber>'" . PHP_EOL);
$client = new ClientDownloadAndProcessSet($setNumber);
$client->selectUrls();
echo $client->processUrls();
echo "All done!" . PHP_EOL;
*/

/*
$urls = array("http://www.retailmenot.com/view/victoriassecret.com","http://www.retailmenot.com/view/bananarepublic.com");
$client = new ClientDownloadAndProcessUrls($urls);
echo $client->processUrls();
echo "All done!" . PHP_EOL;
*/
?>