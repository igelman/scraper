<?php
require_once("../ajax/client-download-and-process-class.php");

class TestClientDownloadAndProcess extends PHPUnit_Framework_TestCase {

	protected function setUp() {
	
		$this->urlsArray = array("http://www.retailmenot.com/view/victoriassecret.com","http://www.retailmenot.com/view/bananarepublic.com");

		$this->urlsClient = new ClientDownloadAndProcessUrls($this->urlsArray);
		$this->setClient = new ClientDownloadAndProcessSet(8);
		
		$set = 15;
		$sleep = 10;
//		$this->setClientWithSleep = new ClientDownloadAndProcessSet($set,$sleep);

		//$this->badUrlsClient = new ClientDownloadAndProcessUrls();

	}
	
	public function testConstruct() {
		$this->assertInstanceOf("ClientDownloadAndProcess", $this->setClient);
		$this->assertInstanceOf("ClientDownloadAndProcessSet", $this->setClient);
		$this->assertInstanceOf("ClientDownloadAndProcess", $this->urlsClient);
		$this->assertInstanceOf("ClientDownloadAndProcessUrls", $this->urlsClient);
		
		//$this->assertInstanceOf("ClientDownloadAndProcessSet", $this->setClientWithSleep);

/*  Maybe the class should throw an exception if it doesn't receive the right arguments
		$usageMessage = "Usage: 'php client-class.php <int setNumber>'";
		$this->assertEquals($usageMessage,)
*/
	}
	
	public function testSelectUrls(){
		
		$this->setClient->selectUrls();
		echo PHP_EOL . "setClient->getUrls(): " . print_r($this->setClient->getUrls(),TRUE) . PHP_EOL;
		$this->assertTrue(count($this->setClient->getUrls()) > 0);

/*
		$this->setClientWithSleep->selectUrls();
		echo PHP_EOL . "setClientWithSleep->getUrls(): " . print_r($this->setClientWithSleep->getUrls(),TRUE) . PHP_EOL;
		$this->assertTrue(count($this->setClientWithSleep->getUrls()) > 0);
*/


		echo PHP_EOL . "urlClient->getUrls(): " . print_r($this->urlsClient->getUrls(),TRUE) . PHP_EOL;
		$this->assertEquals($this->urlsArray,$this->urlsClient->getUrls());
	}
	
	public function testCallback() {
		echo print_r($this->setClient->processUrls(),TRUE) . PHP_EOL;
	}
	
	
}
