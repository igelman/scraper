<?php
require_once("../ajax/semaphore-manager-class.php");

class TestSemaphoreManager extends PHPUnit_Framework_TestCase {

	private $sm;
	
	protected function setUp() {
		$this->sm = new SemaphoreManager();
	}
	
	public function testConstruct() {
		$this->assertInstanceOf("SemaphoreManager", $this->sm);
	}
	
}
