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
		
		$stringTemplate = "<html><head><body><h1>string of html</h1><div class='ItemClass'><title></title>Item 1 div</div><div class='ItemClass'><title></title>Item 2 div</div><div class='ItemClass'><title></title>Item 3 div</div></body></html>";

		$this->rmnString = str_replace("string of html", "rmn html", $stringTemplate) ;
		$this->rmnString = str_replace("class='ItemClass'", "class='RmnItemClass'", $this->rmnString);
		$this->rmnString = str_replace( "<title></title>", "<div class='title'><div></div><h3>Title h3</h3></div>", $this->rmnString);

		$this->dnString =  str_replace("string of html", "dn html", $stringTemplate) ;
		$this->dnString = str_replace("class='ItemClass'", "class='dnItemClass'", $this->dnString);
		
		$this->rmnParser = FileParser::create("RetailmenotParser", $this->rmnString);
		$this->dnParser = FileParser::create("DealnewsParser", $this->dnString);
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
		
		echo $this->rmnString . PHP_EOL;
		echo "*** rmnItems: " . print_r($rmnItems, TRUE). PHP_EOL;
		echo "*** dnItems: " . print_r($dnItems, TRUE). PHP_EOL;
		$this->assertTrue(is_array($rmnItems));
		$this->assertEquals("Item 1 div", $rmnItems[0]['content']);
		$this->assertEquals("Item 1 div", $dnItems[0]['content']);
		
	}






}
?>