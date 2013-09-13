<?php

require_once ("../assets/php/simplehtmldom/simple_html_dom.php");
require_once ("dom-object-manager-class.php");

class FileParser {

	private $htmlDomObjectManager;
	private $domObject;
	private $itemClass;
	private $parsedContent;
	
	protected function __construct($html) {
		$this->htmlDomObjectManager = new HtmlDomObjectManager($html);
		$this->domObject = $this->htmlDomObjectManager->getDomObject();
	}
	
	public function __destruct() {
		$this->htmlDomObjectManager->clearObject($this->domObject );
	}
	
	static function create($subclass, $html) {
		return new $subclass($html);
	}

	public function getDomObject(){
		return $this->domObject;
	}
	
	public function OLDparseDomObject() {
		$itemClass = $this->assignElementClass();
		//$items = array();
		$this->parsedContent = array();
		foreach($this->domObject->find( $itemClass ) as $item) {
			$this->parsedContent[]['content'] = $item->innertext;
			$this->parseItem($item);
		}
		//$this->parsedContent = $items;
	}

	public function parseDomObject() {
		$itemClass = $this->assignElementClass();
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
		$item['title'] = $node->find('div.title', 0)->find('h3', 0)->innertext; // need a way to handle missing 'title' and so suppress the nested find
		
		return $item;
	}
		
}

class DealnewsParser extends FileParser {

	public function assignElementClass() {
		return "div.dnItemClass";
	}

	public function parseItem(simple_html_dom_node $node) {
		$item = array();
		$item['content'] = $node->innertext;
		
		return $item;
	}	
}

?>