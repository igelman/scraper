<?php
require_once("../ajax/array-queuer-class.php");

class TestArrayQueuer extends PHPUnit_Framework_TestCase {
	private $aq;
	private $testArray;
	private $maxLength;

	public function setUp() {
		$this->maxLength = 3;
		$this->testArray = array(
			0,1,2,3,4,5,6,7,8,9,
		);
		
		$this->shortArray = array(
			1,
			2,
		);
		
	
		$this->aq = new ArrayQueuer($this->testArray, $this->maxLength);
		$this->aqShort = new ArrayQueuer($this->shortArray, $this->maxLength);
	}
	
	public function testConstructor() {
		$this->assertInstanceOf("ArrayQueuer", $this->aq);
	}
	
	public function testGetNextSet() {
		$nextSet = $this->aq->getNextSet();
		$this->assertEquals($this->maxLength, count( $nextSet ) );
		$this->assertEquals(count($this->testArray) - $this->maxLength, count( $this->aq->getRemainingArray() ) );
		echo "REGULAR TEST" . PHP_EOL;
		echo "nextSet: " . print_r($nextSet) . PHP_EOL;
		echo "remainingArray: " . print_r($this->aq->getRemainingArray()) . PHP_EOL;
	}
	
	public function testGetNextSetShort() {
		$nextSet = $this->aqShort->getNextSet();
		$this->assertEquals(0, count($this->aqShort->getRemainingArray()));
		echo "SHORT TEST" . PHP_EOL;
		echo "shortArray: " . print_r($this->shortArray, TRUE) . PHP_EOL;
		echo "nextSet: " . print_r($nextSet, TRUE) . PHP_EOL;
		echo "remainingArray: " . print_r($this->aqShort->getRemainingArray(), TRUE) . PHP_EOL;

	}
	
	public function testGetSets() {
		echo "testGetSets (short) :" . print_r($this->aqShort->getSets(), TRUE) . PHP_EOL;
		echo "testGetSets (full) :" . print_r($this->aq->getSets(), TRUE) . PHP_EOL;
		$this->assertEquals(1, count($this->aqShort->getSets()));
		$this->assertEquals(4, count($this->aq->getSets()));
	}
	
	public function testUrls() {
$urls = array(
	"http://www.retailmenot.com/view/1800contacts.com",
	"http://www.retailmenot.com/view/1800flowers.com",
	"http://www.retailmenot.com/view/6pm.com",
	"http://www.retailmenot.com/view/adidas.com",
	"http://www.retailmenot.com/view/ae.com",
	"http://www.retailmenot.com/view/aerie.com",
	"http://www.retailmenot.com/view/aeropostale.com",
	"http://www.retailmenot.com/view/allmodern.com",
	"http://www.retailmenot.com/view/alloy.com",
	"http://www.retailmenot.com/view/amazon.com",
	"http://www.retailmenot.com/view/amazon.com",
	"http://www.retailmenot.com/view/anneklein.com",
	"http://www.retailmenot.com/view/anntaylor.com",
	"http://www.retailmenot.com/view/apple.com",
	"http://www.retailmenot.com/view/ardenb.com",
	"http://www.retailmenot.com/view/armanibeauty.com",
	"http://www.retailmenot.com/view/art.com",
	"http://www.retailmenot.com/view/athleta.com",
	"http://www.retailmenot.com/view/aveda.com",
	"http://www.retailmenot.com/view/aveeno.com",
	"http://www.retailmenot.com/view/avenue.com",
	"http://www.retailmenot.com/view/aveyou.com",
	"http://www.retailmenot.com/view/backcountry.com",
	"http://www.retailmenot.com/view/bananarepublic.com",
	"http://www.retailmenot.com/view/barenecessities.com",
	"http://www.retailmenot.com/view/barnesandnoble.com",
	"http://www.retailmenot.com/view/barneys.com",
	"http://www.retailmenot.com/view/bathandbodyworks.com",
	"http://www.retailmenot.com/view/bealls.com",
	"http://www.retailmenot.com/view/beauty.com",
	"http://www.retailmenot.com/view/beautybar.com",
	"http://www.retailmenot.com/view/beautybay.com",
	"http://www.retailmenot.com/view/beautybrands.com",
	"http://www.retailmenot.com/view/beautyencounter.com",
	"http://www.retailmenot.com/view/beautyhabit.com",
	"http://www.retailmenot.com/view/beautyofnewyork.com",
	"http://www.retailmenot.com/view/bebe.com",
	"http://www.retailmenot.com/view/benefitcosmetics.com",

);
		$aqUrls = new ArrayQueuer($urls, 10);
		echo "urls :" . print_r($aqUrls->getSets(), TRUE) . PHP_EOL;

	}


}

?>