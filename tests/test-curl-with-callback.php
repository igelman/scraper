<?php
require_once("../ajax/curl-with-callback-class.php");

class TestCurlWithCallback extends PHPUnit_Framework_TestCase {

	private $cc;
	
	protected function setUp() {
		$url = "http://google.com";
		$callback = function($ch){return "I'm a callback";};
		$this->cc = new CurlWithCallback($url, $callback);
		$this->cNoC = new CurlWithCallback($url);
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

}