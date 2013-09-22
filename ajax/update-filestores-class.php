<?php
require_once("../ajax/file-downloader-class.php");
require_once("../ajax/pdo-manager-class.php");
require_once("../config/local.config"); // APP_ROOT_PATH | FILE_STORE_PATH | DB_HOST | DB | DB_USER | DB_PASS

class UpdateFilestores {
	private $setNumber;
	private $urlSet;
	private $pm; // PDO connection handle
	
	public function __construct($setNumber) {
		$this->setNumber = $setNumber;
		
		$this->pm = PdoManager::getInstance();
		$this->selectUrlSet();
	}
	
	private function selectUrlSet(){
		try {
			$setNumber = $this->setNumber;		
			$stmt = $this->pm->query("SELECT url FROM files WHERE set_number = $setNumber");
			$stmt->bindParam(':setNumber', $this->setNumber);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$this->urlSet = array();
			while($r = $stmt->fetchColumn()){
				echo $r . PHP_EOL;
				$this->urlSet[] = $r;
			}
			
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}		
	}
	
	public function getUrlSet(){
		return $this->urlSet;
	}
}

?>