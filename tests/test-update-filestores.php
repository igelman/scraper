<?php
require_once("../ajax/update-filestores-class.php");

class TestUpdateFilestores extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->uf = new UpdateFilestores();
		$this->setNumber = 2; // (0 - 2 complete)
	}
	
	public function testConstruct() {
		$this->assertInstanceOf("UpdateFilestores", $this->uf);
	}
	
	public function testChooseSet(){
		$this->uf->chooseSet($this->setNumber);
		$this->assertEquals(10, count($this->uf->getUrlSet()));
	}
	
	public function testDownloadSet() {
		$this->uf->chooseSet($this->setNumber);
		$this->uf->downloadSet();
		echo $this->uf->checkResults();
/*
		$this->pm = PdoManager::getInstance();
		
		$stmt = $this->pm->prepare("SELECT OCTET_LENGTH(content) FROM files WHERE url = :url");
		$stmt->bindParam(':url', $url);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
				
		$urls = $this->uf->getUrlSet();
		foreach ($urls as $url) {
			$stmt->execute();
			$result = $stmt->fetchColumn();
			echo "testDownloadSet $url content length: " . print_r($result) . PHP_EOL;
			$this->assertTrue($result > 0);
		}
*/
	}
}
