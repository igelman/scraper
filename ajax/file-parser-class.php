<?php

require_once ("../assets/php/simplehtmldom/simple_html_dom.php");
require_once ("dom-object-manager-class.php");

class FileParser {

	private $htmlDomObjectManager;
	private $domObject;
	private $itemClass;
	private $parsedContent;
	
	protected function __construct($html, $type) {
		$objectManagerClass = $type . "DomObjectManager";
		$this->htmlDomObjectManager = new $objectManagerClass($html); //HtmlDomObjectManager($html);
		$this->domObject = $this->htmlDomObjectManager->getDomObject();
	}
	
	public function __destruct() {
		$this->htmlDomObjectManager->clearObject($this->domObject );
	}

	/**
	* Create parser from html string
	*/	
	static function createFromHtml($subclass, $html) {
		return new $subclass($html, "html");
	}

	/**
	* Create parser from html file (or remote url)
	*/	
	static function createFromFile($subclass, $file) {
		return new $subclass($file, "file");
	}


	public function getDomObject(){
		return $this->domObject;
	}
	
	public function parseDomObject() {
		$itemClass = $this->assignElementClass();
		echo "parseDomObject itemClass: " . $itemClass . PHP_EOL;
		$this->parsedContent = array();
		foreach($this->domObject->find( $itemClass ) as $node) {
			$this->parsedContent[] = $this->parseItem($node);
		}
	}

	public function getParsedContent() {
		return $this->parsedContent;
	}


}

class RetailmenotParser extends FileParser {

	public function assignElementClass() {
		return "div.RmnItemClass";
	}
	
	public function parseItem(simple_html_dom_node $node) {
		$item = array();
		$item['content'] = $node->innertext;
		$item['title'] = is_object($node->find('div.title', 0) ) ? $node->find('div.title', 0)->find('h3', 0)->innertext : "";
		
		return $item;
	}
		
}

class DealnewsParser extends FileParser {

	public function assignElementClass() {
		return "div.DnItemClass";
	}

	public function parseItem(simple_html_dom_node $node) {
		$item = array();
		$item['content'] = $node->innertext;
		
		return $item;
	}	
}

?>