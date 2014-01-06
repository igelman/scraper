<?php
require_once("../ajax/semaphore-manager-class.php");

class TestSemaphoreManager extends PHPUnit_Framework_TestCase {

	private $sm;
	private $dirname = "/Users/alantest/Downloads/";
	private $basename = "testFile.txt";
	private $content = "A string of content";
	
	protected function setUp() {
		$this->sm = new SemaphoreManager($this->dirname, $this->basename, $this->content);
	}
	
	protected function tearDown() {
		$this->sm->removeSemaphore();
	}
	
	public function testConstruct() {
		$this->assertInstanceOf("SemaphoreManager", $this->sm);
//		$this->assertEquals($this->dirname . $this->basename, $this->sm->getPath());
		$this->assertEquals($this->content, $this->sm->getContent());
	}
	
	public function testCreateSemaphore() {
		$this->assertTrue($this->sm->createSemaphore() > 0);
	}
	
	public function testRemoveSemaphore() {
		$this->assertTrue($this->sm->removeSemaphore());
	}
	
	public function testSemaphoreExists() {
		$this->assertFalse($this->sm->semaphoreExists());
		$this->sm->createSemaphore();
		$this->assertTrue($this->sm->semaphoreExists());
	}
	
	public function testReadSemaphore() {
		$this->assertFalse($this->sm->readSemaphore());
		$this->sm->createSemaphore();
		$this->assertEquals($this->content, $this->sm->readSemaphore());
	}
	
	public function testSendSemaphoreContents() {
		$this->assertFalse($this->sm->sendSemaphoreContents("alan@igelman.com", "alert"));
		$this->sm->createSemaphore();
		$this->assertTrue($this->sm->sendSemaphoreContents("alan@igelman.com", "alert"));
	}
	
	public function testSetContent() {
		$newContent = "This is new content";
		$this->sm->setContent($newContent);
		$this->assertEquals($newContent, $this->sm->getContent());
	}
}
