<?php
require_once("../ajax/client-download-and-process-class.php");

class TestClientDownloadAndProcess extends PHPUnit_Framework_TestCase {

	protected function setUp() {
        date_default_timezone_set('America/New_York');
	
		$this->urlsArray = [
            "http://www.retailmenot.com/view/fossil.com",
            "http://www.retailmenot.com/view/fragrancenet.com "
        ];
		//array("http://www.retailmenot.com/view/fossil.com","http://www.retailmenot.com/view/fragrancenet.com ");

		$set = 2;
		$sleep = 15;

		$this->urlsClient = new ClientDownloadAndProcessUrls($this->urlsArray, 20);
		$this->setClient = new ClientDownloadAndProcessSet(/*41*/$set);
		
		$this->setClientWithSleep = new ClientDownloadAndProcessSet($set,$sleep);

		//$this->badUrlsClient = new ClientDownloadAndProcessUrls();

	}
	
	public function testConstruct() {
		$this->assertInstanceOf("ClientDownloadAndProcess", $this->setClient);
		$this->assertInstanceOf("ClientDownloadAndProcessSet", $this->setClient);
		$this->assertInstanceOf("ClientDownloadAndProcess", $this->urlsClient);
		$this->assertInstanceOf("ClientDownloadAndProcessUrls", $this->urlsClient);
		
		$this->assertInstanceOf("ClientDownloadAndProcessSet", $this->setClientWithSleep);

/*  Maybe the class should throw an exception if it doesn't receive the right arguments
		$usageMessage = "Usage: 'php client-class.php <int setNumber>'";
		$this->assertEquals($usageMessage,)
*/
	}
	
	public function testSelectUrlsReturnsUrlsExpectedToBeDownloaded(){
		echo PHP_EOL . "testSelectUrlsReturnsUrlsExpectedToBeDownloaded running ..." . PHP_EOL;
		
		echo PHP_EOL . "setClient->getUrls(): " . print_r($this->setClient->getUrls(),TRUE) . PHP_EOL;
		$this->assertTrue(count($this->setClient->getUrls()) > 0);

		echo PHP_EOL . "setClientWithSleep->getUrls(): " . print_r($this->setClientWithSleep->getUrls(),TRUE) . PHP_EOL;
		$this->assertTrue(count($this->setClientWithSleep->getUrls()) > 0);


		echo PHP_EOL . "urlClient->getUrls(): " . print_r($this->urlsClient->getUrls(),TRUE) . PHP_EOL;
		$this->assertEquals($this->urlsArray,$this->urlsClient->getUrls());
	}
	
	public function testCallbackReturnsAndSleepInterval() {
		echo PHP_EOL . "testCallbackReturnsAndSleepInterval running ..." . PHP_EOL;

/*
		$setClientProcessUrlsOutput = $this->setClientWithSleep->processUrls();
		echo "\$setClientProcessUrlsOutput: " . print_r($setClientProcessUrlsOutput,TRUE) . PHP_EOL;
		$this->assertEquals(sizeof($this->setClientWithSleep->getUrls()), sizeof($setClientProcessUrlsOutput), "Size of processUrls array is the number of urls requested");
*/
		
		$urlClientProcessUrlsOutpout = $this->urlsClient->processUrls();
		echo "\$urlClientProcessUrlsOutpout: " . print_r($urlClientProcessUrlsOutpout,TRUE) . PHP_EOL;
		$this->assertEquals(sizeof($this->urlsClient->getUrls()), sizeof($urlClientProcessUrlsOutpout), "Size of processUrls array is the number of urls requested");

	}
	
	
}
