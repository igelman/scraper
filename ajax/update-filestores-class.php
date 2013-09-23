<?php
require_once("../ajax/file-downloader-class.php");
require_once("../ajax/pdo-manager-class.php");
require_once("../config/local.config"); // APP_ROOT_PATH | FILE_STORE_PATH | DB_HOST | DB | DB_USER | DB_PASS

/**
* Download a set of urls and store content in db scraper table "files"
* __construct
* chooseSet($setNumber) for a set of urls (from table "files")
* downloadSet() to store the set's html in files
*/
class UpdateFilestores {
	private $setNumber;
	private $urlSet;
	private $pm; // PDO connection handle
	
	public function __construct() {
		$this->pm = PdoManager::getInstance();
	}
	
	public function chooseSet($setNumber) {
		$this->setNumber = $setNumber;
		$this->selectUrlSet();
	}
	
	public function downloadSet() {
		$urls = $this->urlSet;
		$fd2db = new FiletoBlobDownloader($urls);
		$fd2db->createCurlMultiHandler();
		$fd2db->storeFiles();
	}
	
	private function selectUrlSet(){
		try {
			$stmt = $this->pm->prepare("SELECT url FROM files WHERE set_number = :setNumber");
			$stmt->bindParam(':setNumber', $this->setNumber);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->execute();
			$this->urlSet = array();
			while($r = $stmt->fetchColumn()){
				$this->urlSet[] = $r;
			}
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}		
	}
	
	public function getUrlSet() {
		return $this->urlSet;
	}
	
	public function checkResults() {
		$stmt = $this->pm->prepare("SELECT OCTET_LENGTH(content) FROM files WHERE url = :url");
		$stmt->bindParam(':url', $url);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);

		$this->statusMessage = "";
		$count = 0;
		foreach($this->urlSet as $url) {
			$stmt->execute();
			$result = $stmt->fetchColumn();
			$successCount += $result ? 1:0;
			$count++;
			$this->statusMessage .= "$url blob size: $result" . PHP_EOL;
		}
		
		$this->statusMessage = "Downloaded $successCount of " . $count . PHP_EOL . $this->statusMessage;
		return $this->statusMessage;
	}
}

?>