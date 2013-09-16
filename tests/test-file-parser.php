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
	private $rmnItems0Title;

	public function setUp() {
		
		$this->rmnFile = "sample-files/gamefly-20130904-1140.html"; // "http://www.retailmenot.com/view/gamefly.com";
		$this->rmnItems0Title = " 20% Off Digital Downloads "; // First article's title
		$this->dnFile = "sample-files/dealnews-20130913.html";
	
	
		$this->rmnString = "<li id=\"c5016722\" class=\"offer clearfix coupon pop\" data-offerid=\"5016722\" data-couponscore=\"67\" data-couponrank=\"0.72\" data-couponid=\"parent.madreId\" data-siteid=\"137559\" data-storename=\"GameFly\" data-storedomain=\"gamefly.com\" data-titleslug=\"20-percent-off-digital-downloads\" ><div class=\"detail\"> <div class=\"description\"> <div class=\"title\"> <h3> 20% Off Digital Downloads </h3> </div> <div class=\"codelabel\">coupon code:</div> <div class=\"crux attachFlash\"> <div class=\"cover\">Show coupon code</div> <div class=\"label\">&nbsp;</div> <div class=\"code\">GFDSEP20</div> </div> <p class=\"discount\"> GameFly: Shop Now and Save 20% Off Digital Downloads with Coupon Code! Valid through 9/30/13 only. </p> </div> <ul class=\"offer_status\" data-expires=\"2013-09-30 23:59:59\" data-last-click=\"-4436000\" data-num-clicks-today=\"57\"> <li class=\"metadata1 border-right\">57 people used today</li> <li class=\"metadata2\">Last used 1 hour ago</li> </ul> <ul class=\"actions\"> <li class=\"commentsTrigger\"> <span class=\"commentBubble\">2</span> <span class=\"commentLink\">Comments</span> </li> <li class=\"shareTrigger faux-link\" >Share</li> </ul> </div> <div class=\"voting\"> <div class=\"rating high\"> <div class=\"percent\">67<span>%</span></div> <div class=\"success\">Success</div> </div> <div class=\"thumbs\"> <span class=\"up canVote\"><span class=\"tooltip\">good coupon</span></span> <span class=\"down canVote\"><span class=\"tooltip\">bad coupon</span></span> </div> <div class=\"vote_count\">3 votes</div> </div> <div class=\"offer_detail comment_detail\"> <div class=\"detail_close\"><span>X</span></div> <div class=\"comments\"> <div class=\"comment_count\">Showing 2 most recent comments</div> <ul class=\"comment_list\"> <li class=\"comment up\"> <p class=\"comment_description\"> Saved $17.00 on Rome Total War 2 (09/04/2013) </p> <p class=\"comment_author\"> - Anonymous, 13 hours ago</p> </li><li class=\"comment\"> <p class=\"comment_description\"> Awesome! Really worked. Thanks!!! </p> <p class=\"comment_author\"> - Anonymous, 13 hours ago</p> </li> </ul> </div> </div></li>";

		
/*
		$this->rmnFileParser = FileParser::createFromFile("RetailmenotParser", $this->rmnFile);		
		$this->rmnParser = FileParser::createFromHtml("RetailmenotParser", $this->rmnString);
*/

//This caused a memory size error
		$this->dnFileParser = FileParser::createFromFile("DealnewsParser", $this->dnFile);

	}
	
	public function testConstruct() {
/*
		$this->assertInstanceOf("RetailmenotParser", $this->rmnFileParser);
		$this->assertInstanceOf("RetailmenotParser", $this->rmnParser);
*/
		$this->assertInstanceOf("DealnewsParser", $this->dnParser);
	}
	
	
	public function testParseDomObject() {
/*
		$this->rmnFileParser->parseDomObject();
		$rmnFileItems = $this->rmnFileParser->getParsedContent();
		$this->assertTrue(is_array($rmnFileItems));
		echo print_r($rmnFileItems,TRUE);
		$this->assertEquals($this->rmnItems0Title, $rmnFileItems[0]['title']);

		$this->rmnParser->parseDomObject();
		$rmnItems = $this->rmnParser->getParsedContent();
		$this->assertTrue(is_array($rmnItems));
		$this->assertEquals($this->rmnItems0Title, $rmnItems[0]['title']);
*/
	
		
		$this->dnParser->parseDomObject();
		$dnItems = $this->dnParser->getParsedContent();
		//$this->assertEquals($this->dnContent['title'], $dnItems[0]['title']);		
	}


}
?>