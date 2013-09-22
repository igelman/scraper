<?php
require_once("../ajax/update-filestores-class.php");

class TestUpdateFilestores extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->uf = new UpdateFilestores(1);
	}
	
	public function testConstruct() {
		$this->assertInstanceOf("UpdateFilestores", $this->uf);
		$this->assertEquals(10, count($this->uf->getUrlSet()));
	}
}