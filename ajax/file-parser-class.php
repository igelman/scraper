<?php

require_once ("../assets/php/simplehtmldom/simple_html_dom.php");
require_once ("dom-object-manager-class.php");

class FileParser {

	private $htmlDomObjectManager;
	private $domObject;
	private $itemClass;
	private $parsedContent;
	
	protected function __construct($html, $type) {
		$objectManagerClass = $type . "DomObjectManager";  // "html" or "file"
		$this->htmlDomObjectManager = new $objectManagerClass($html);
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
		//echo "parseDomObject itemClass: " . $itemClass . PHP_EOL;
		$this->parsedContent = array();
		foreach($this->domObject->find( $itemClass ) as $node) {
			$this->parsedContent[] = $this->parseItem($node);
		}
	}

	public function getParsedContent() {
		return $this->parsedContent;
	}

	/*
	* Find the innermost content for the nested elements 
	*/
	protected function checkNestedElements($node, $elementsArray) {
		foreach($elementsArray as $element) {
			if ( !is_object( $node->find($element, 0) ) ) {
				return "";
			}
			$node = $node->find($element, 0);
		}
		//echo "checkNestedElements: " . implode(":", $elementsArray) . " -- " . $node->innertext . PHP_EOL;
		return $node->innertext;
	}


}

class RetailmenotParser extends FileParser {

	/**
	* Return the element class that distinguishes article node
	* 
	*/
	public function assignElementClass() {
		return "li.offer";
	}
		
	/**
	* Parses an article node for various content
	* Returns content in an array.
	*
	* This function needs to be manually created for
	*  each source type. It's basically a mapping of
	*  element classes.
	*/
	public function parseItem(simple_html_dom_node $node) {
		$item = array();
		
		$article_outer = $node->outertext; // I use this later to find some stuff that simple_html_dom can't parse on its own
		//echo "***" . PHP_EOL . $article_outer . PHP_EOL . "***" . PHP_EOL;
		//$item['content'] = $node->innertext;
		
		$item['title'] = $this->checkNestedElements($node, array('div.title', 'h3'));
		$item['deal_type'] = $this->checkNestedElements($node, array('div.title', 'div.type_icon'));
		$item['details'] = $this->checkNestedElements($node, array('p.discount'));
		$item['coupon'] = $this->checkNestedElements($node, array('div.code'));

		// The regex patterns fail if the attribute is empty string
		//  leading to the ugly kludge in the "expires" element
		$storename_pattern = '/data-storename="(.+?)"/';
		preg_match($storename_pattern, $article_outer, $storename_matches);
		$item['merchant'] = $storename_matches[1];
		
		$storedomain_pattern = '/data-storedomain="(.+?)"/';
		preg_match($storedomain_pattern, $article_outer, $storedomain_matches);
		$item['merchant_domain'] = $storedomain_matches[1];

		$expires_pattern = '/data-expires="(.+?)"/';
		preg_match($expires_pattern, $article_outer, $expires_matches);
		$item['expires'] = strpos($expires_matches[1], "data-last-click=")  ?  "" : $expires_matches[1];
				
		$last_click_pattern = '/data-last-click="(.+?)"/';
		preg_match($last_click_pattern, $article_outer, $last_click_matches);
		$item['last_click'] = $last_click_matches[1];
				
		$num_clicks_today_pattern = '/data-num-clicks-today="(.+?)"/';
		preg_match($num_clicks_today_pattern, $article_outer, $num_clicks_today_matches);
		$item['num_clicks_today'] = $num_clicks_today_matches[1];

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