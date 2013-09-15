<?php
include("../ajax/file-parser-class.php");

class testFileParser extends PHPUnit_Framework_TestCase {
	private $rmnParser;
	private $dnParser;
	private $rmnString;
	private $dnString;
	private $rmnDom;
	private $dnDom;
	
	private $rmnFile;
	private $dnFile;

	public function setUp() {
		
		$this->rmnFile = "../data/sample-files/gamefly-20130904-1140.html";
		$this->dnFile = "../data/sample-files/dealnews-20130913.html";
	
	
		/***********
		* Mock up strings to test parser
		************/
		$this->rmnContent = array(
			array(
				"title"			=>	"rmnTitle 1",
				"description"	=>	"rmnDesc 1",
			),
			array(
				"title"			=>	"rmnTitle 2",
				"description"	=>	"rmnDesc 2",
			),
			array(
				"title"			=>	"rmnTitle 3",
				"description"	=>	"rmnDesc 3",
			),
		);
		
		$this->dnContent = array(
			array(
				"title"			=>	"dnTitle 1",
				"description"	=>	"dnDesc 1",
			),						 
			array(					 
				"title"			=>	"dnTitle 2",
				"description"	=>	"dnDesc 2",
			),						 
			array(					 
				"title"			=>	"dnTitle 3",
				"description"	=>	"dnDesc 3",
			),
		);
		
		$htmlString = "<html><head><body><h1>string of html</h1>";
		foreach($this->rmnContent as $item) {
			$htmlString .= "<div class='RmnItemClass'>";
			$htmlString .= "<div class='title'><div></div><h3>" . $item['title'] . "</h3></div>";
			$htmlString .= "<div>" . $item['description'] . "</div>";
		}
		$htmlString .= "</body></html>";
		$this->rmnString = $htmlString;

		$htmlString = "<html><head><body><h1>string of html</h1>";
		foreach($this->dnContent as $item) {
			$htmlString .= "<div class='DnItemClass'>";
			$htmlString .= "<div class='title'><div></div><h3>" . $item['title'] . "</h3></div>";
			$htmlString .= "<div>" . $item['description'] . "</div>";
		}
		$htmlString .= "</body></html>";
		$this->dnString = $htmlString;
		/************
		************/
		
		
		$this->rmnParser = FileParser::createFromHtml("RetailmenotParser", $this->rmnString);
		$this->dnParser = FileParser::createFromHtml("DealnewsParser", $this->dnString);

/*
This caused a memory size error
		$this->rmnFileParser = FileParser::createFromFile("RetailmenotParser", $this->rmnFile);
		$this->dnFileParser = FileParser::createFromFile("DealnewsParser", $this->dnFile);
*/

	}

	
	public function testConstruct() {
		$this->assertInstanceOf("RetailmenotParser", $this->rmnParser);
		$this->assertInstanceOf("DealnewsParser", $this->dnParser);
	}
	
	
	public function testParseDomObject() {
		$this->rmnParser->parseDomObject();
		$this->dnParser->parseDomObject();
		$rmnItems = $this->rmnParser->getParsedContent();
		$dnItems = $this->dnParser->getParsedContent();
		
//		echo $this->rmnString . PHP_EOL;
//		echo "*** rmnItems: " . print_r($rmnItems, TRUE). PHP_EOL;
//		echo "*** dnItems: " . print_r($dnItems, TRUE). PHP_EOL;
		$this->assertTrue(is_array($rmnItems));
//		echo PHP_EOL . PHP_EOL;
//		echo "rmnItems[0][title]: " . $rmnItems[0]['title'] . PHP_EOL;
//		echo "this->rmnContent[0][title] " . $this->rmnContent[0]['title'] . PHP_EOL;
		$this->assertEquals($this->rmnContent[0]['title'], $rmnItems[0]['title']);
//		$this->assertEquals($this->dnContent['title'], $dnItems[0]['title']);
		
	}






}
?>