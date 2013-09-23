<?php
require_once("../ajax/update-filestores-class.php");

class TestUpdateFilestores extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->uf = new UpdateFilestores();
		$this->setNumber = 7; // (0 - 6 complete)
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
		$statusMessage = $this->uf->checkResults();
		$this->assertTrue(substr($statusMessage, 0, 1) >= 1);
		echo $statusMessage;
		
	}
}
