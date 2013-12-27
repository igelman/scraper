<?php
require_once("../ajax/curl-with-callback-class.php");

class TestCurlWithCallback extends PHPUnit_Framework_TestCase {

	private $cc;
	private $cNoC;
	private $badC;
	
	protected function setUp() {
		$url = "http://google.com";
		$callback = function($ch){return "I'm a callback";};
		$this->cc = new CurlWithCallback($url, $callback);
		$this->cNoC = new CurlWithCallback($url);
		$badUrl = "http://igelman.com/not-a-file";
		$this->badC = new CurlWithCallback($badUrl);

	}

	public function testConstruct() {
		$this->assertInstanceOf('CurlWithCallback',$this->cc);
		$this->assertInstanceOf('CurlWithCallback',$this->cNoC); // tests the curl with NO callback
	}
	
	public function testExecuteCurl() {
		$this->cc->executeCurl();
		$this->assertEquals("I'm a callback", $this->cc->getCallbackReturn());
		
		$this->cNoC->executeCurl();
		$this->assertNull($this->cNoC->getCallbackReturn());
	}
	
	public function testGetCurlResult() {
		$this->cc->executeCurl();
		$this->assertTrue((boolean)stripos($this->cc->getCurlResult(), "Search the world's information"));
	}
	
	public function testGetCurlError() {
		$this->cc->executeCurl();
		$this->assertNull($this->cc->getCurlError());
		
		$this->badC->executeCurl();
		//echo "result: " . $this->badC->getCurlResult() . PHP_EOL;
		//echo "error: " . $this->badC->getcurlError() . PHP_EOL;
	}
	
	public function testGetCurlHandle() {
		$this->assertEquals("curl", get_resource_type($this->cc->getCurlHandle()));
	}
}