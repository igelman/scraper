<?php
include("../ajax/file-parser-class.php");

class testFileParser extends PHPUnit_Framework_TestCase {
	private $rmnParser;
	private $dnParser;
	private $rmnString;
	private $dnString;
	private $rmnDom;
	private $dnDom;

	public function setUp() {
		$this->rmnParser = FileParser::create("RetailmenotParser");
		$this->dnParser = FileParser::create("DealnewsParser");
		
		$stringTemplate = "<html><head><body><h1>string of html</h1><div>A div</div></body></html>";
		$this->rmnString = str_replace("string of html", "rmn html", $stringTemplate) ;
		$this->dnString =  str_replace("string of html", "dn html", $stringTemplate) ;
	}
	
	public function testConstruct() {
		$this->assertInstanceOf("RetailmenotParser", $this->rmnParser);
		$this->assertInstanceOf("DealnewsParser", $this->dnParser);
	}
	
	public function testCreateDomObject() {
		$this->rmnDom = $this->rmnParser->createDomObject($this->rmnString);
		$this->dnDom = $this->dnParser->createDomObject($this->dnString);
		echo "rmnDom class: " . get_class($this->rmnDom);
/*
		$this->rmnParser->clearObject($rmnDom);
		$this->dnParser->clearObject($dnDom);
*/
	}






}
?>