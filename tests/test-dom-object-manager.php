<?php
include("../ajax/dom-object-manager-class.php");

class testDomObjectManager extends PHPUnit_Framework_TestCase {

	private $fileDomgr; // DomObjectManager object
	private $htmlDomgr;
	private $html;
	private $file;
	
	public function setUp() {
		$this->file = "http://www.retailmenot.com/view/gamefly.com"; // "sample-files/gamefly-20130904-1140.html"; // "sample-files/dealnews-20130913.html"; //
		$this->html = "<html><head><body><h1>string of html</h1><div class='ItemClass'>Item 1 Div</div><div class='ItemClass'>Item 2 div</div><div class='ItemClass'>Item 3 div</div></body></html>";
		$this->fileDomgr = new FileDomObjectManager($this->file);
		$this->htmlDomgr = new HtmlDomObjectManager($this->html);
	}
	

	public function tearDown() {
		//$this->fileDomgr->clearObject($this->fileDomgr->getDomObject() );
		//$this->htmlDomgr->clearObject($this->htmlDomgr->getDomObject() );
	}


	public function testCreateDomObjectManager() {

		$this->assertInstanceOf("fileDomObjectManager", $this->fileDomgr);
		$this->assertInstanceOf("HtmlDomObjectManager", $this->htmlDomgr);
	}
	
	public function testCreateDomObject() {
	
		$this->assertInstanceOf("simple_html_dom", $this->fileDomgr->getDomObject() );
		$this->assertInstanceOf("simple_html_dom", $this->htmlDomgr->getDomObject() );
	}

}

?>