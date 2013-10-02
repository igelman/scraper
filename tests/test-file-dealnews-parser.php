<?php
require_once("../ajax/file-parser-class.php");

class TestFileDealnewsParser extends PHPUnit_Framework_TestCase {
	private $rmnParser;
	private $dnParser;
	private $rmnString;
	private $dnString;
	private $rmnDom;
	private $dnDom;
	
	private $rmnFile;
	private $dnFile;
	private $rmnItems0Title;

	public function setUp() {
		
		$this->dnFile = "sample-files/dealnews-20131001.html"; //dealnews-20130913.html"; //"http://dealnews.com";// 
		$this->dnFileParser = FileParser::createFromFile("DealnewsParser", $this->dnFile);

	}
	
	public function testConstruct() {
		$this->assertInstanceOf("DealnewsParser", $this->dnFileParser, "assertInstanceOf(\"DealnewsParser\", this->dnParser");
	}
	
	
	public function testParseDomObject() {			
		$this->dnFileParser->parseDomObject();
		$dnItems = $this->dnFileParser->getParsedContent();
//		$this->assertEquals($this->dnContent['title'], $dnItems[0]['title']);
		var_dump($dnItems);
	}


}
?>