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
		
		$this->dnFile = "http://dealnews.com";// "sample-files/dealnews-20131001.html"; //dealnews-20130913.html"; //"http://dealnews.com";// 
		$this->dnFileParser = FileParser::createFromFile("DealnewsParser", $this->dnFile);

	}
	
	public function testConstruct() {
		$this->assertInstanceOf("DealnewsParser", $this->dnFileParser, "assertInstanceOf(\"DealnewsParser\", this->dnParser");
	}
	
	
	public function testParseDomObject() {			
		$this->dnFileParser->parseDomObject();
		$dnItems = $this->dnFileParser->getParsedContent();
		$hotnessMenuItems = $this->dnFileParser->getHotnessMenuItems();
//		$this->assertEquals($this->dnContent['title'], $dnItems[0]['title']);
		var_dump($dnItems);
		var_dump($hotnessMenuItems);
		file_put_contents("../data/html/fdp-test.txt", print_r($dnItems, TRUE));
	}


}
?>