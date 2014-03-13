<?php

require_once (__DIR__ . "/../assets/php/simplehtmldom/simple_html_dom.php");
require_once (__DIR__ . "/dom-object-manager-class.php");

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
		//echo "called FileParser->parseDomObject()" . PHP_EOL;
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
	protected function checkNestedElements($node, $elementsArray, $attribute="innertext") {
		foreach($elementsArray as $element) {
			if ( !is_object( $node->find($element, 0) ) ) {
				return "";
			}
			$node = $node->find($element, 0);
		}
		//echo "checkNestedElements: " . implode(":", $elementsArray) . " -- " . $node->innertext . PHP_EOL;
		return $node->$attribute;
	}


}

class RetailmenotParser extends FileParser {

	/**
	* Return the element class that distinguishes article node
	* 
	*/
	public function assignElementClass() {
		return ".offer"; // used to be li.offer, now it's div.offer
	}
		
	/**
	* Parses an article node for various content
	* Returns content in an array.
	*
	* This function needs to be manually created for
	*  each source type. It's basically a mapping of
	*  element classes.
	*/
	
	protected function useAlternativeStructure($string, $node, $elementsArray, $attribute="innertext") {
		if (strlen($string)==0) {
			return $this->checkNestedElements($node, $elementsArray, $attribute);
		}
		return $string;
	}
	
	public function parseItem(simple_html_dom_node $node) {
		//echo "called class RetalmenotParser->parseItem()" . PHP_EOL;
		$item = array();
		
		$article_outer = $node->outertext; // I use this later to find some stuff that simple_html_dom can't parse on its own
		//echo "***" . PHP_EOL . $article_outer . PHP_EOL . "***" . PHP_EOL;
		//$item['content'] = $node->innertext;
		
		$item['title'] = $this->checkNestedElements($node, array('h2.title'));
		$item['title'] = $this->useAlternativeStructure($item['title'], $node, array('div.title','h3'));
		
		$item['details'] = $this->checkNestedElements($node, array('.description-wrapper','.description'));
		$item['details'] = $this->useAlternativeStructure($item['details'], $node, array('.detail','.description','p.discount'));
		
		$item['coupon'] = $this->checkNestedElements($node, array('.code-text'));
		$item['coupon'] = $this->useAlternativeStructure($item['coupon'], $node, array('.description','.crux','.code'));
		
		$item['use-data'] = $this->checkNestedElements($node, array('.use-data'));
		if (strlen($item['use-data']) == 0) {
			$usedata_pattern = '/data-num-clicks-today="(.+?)"/';
			preg_match($usedata_pattern, $article_outer, $usedata_matches);
			$item['use-data'] = isset($usedata_matches[1]) ? $usedata_matches[1] : "";
		}

//<ul class="offer_status" data-expires="2013-09-29 23:59:59" data-last-click="-4194000" data-num-clicks-today="252">

		// The regex patterns fail if the attribute is empty string
		//  leading to the ugly kludge in the "expires" element
/*
* This attribute was eliminated ~ 9/23/13 and not replaced
		$storename_pattern = '/data-storename="(.+?)"/';
		preg_match($storename_pattern, $article_outer, $storename_matches);
		$item['merchant'] = $storename_matches[1];
*/

		$offerid_pattern = '/data-offerid="(.+?)"/';
		preg_match($offerid_pattern, $article_outer, $offerid_matches);
		$item['offer_id'] = isset($offerid_matches[1]) ? $offerid_matches[1] : "";

		$datatype_pattern = '/data-type="(.+?)"/';
		preg_match($datatype_pattern, $article_outer, $datatype_matches);
		$item['data_type'] = isset($datatype_matches[1]) ? $datatype_matches[1] : "";
		
		$storedomain_pattern = '/data-storedomain="(.+?)"/';
		preg_match($storedomain_pattern, $article_outer, $storedomain_matches);
		$item['merchant_domain'] = isset($storedomain_matches[1]) ? $storedomain_matches[1] : "";

		$couponscore_pattern = '/data-couponscore="(.+?)"/';
		preg_match($couponscore_pattern, $article_outer, $couponscore_matches);
		$item['coupon_score'] = isset($couponscore_matches[1]) ? $couponscore_matches[1] : "";

		$couponrank_pattern = '/data-couponrank="(.+?)"/';
		preg_match($couponrank_pattern, $article_outer, $couponrank_matches);
		$item['coupon_rank'] = isset($couponrank_matches[1]) ? $couponrank_matches[1] : "";




/*
* This 'data-expires' attribute disappeared around 9/22/13.
* Now there's something like this: <li class="bullet">Expires 9/30/2013</li>
		$expires_pattern = '/data-expires="(.+?)"/';
*/
		//$expires_pattern = '/Expires (1?\d\/[1-3]?\d\/201\d)/';
		$expires_pattern = '/(Expires|On|Ends)\s((([012])?\d\/[0123]?\d\/(20)?\d\d))/';
		preg_match($expires_pattern, $article_outer, $expires_matches);
		$item['expires'] = isset( $expires_matches[2] ) ?  $expires_matches[2] : "" ;
		if (strlen($item['expires'] == 0)) {
			unset($expires_matches);
			$expires_pattern = '/data-expires="(.+?)"/';
			preg_match($expires_pattern, $article_outer, $expires_matches);
			$item['expires'] = isset( $expires_matches[1] ) ?  $expires_matches[1] : "" ;
			if (strstr($item['expires'], "data-last-click")) $item['expires'] = "";
		}
				
		$last_click_pattern = '/data-last-click="(.+?)"/';
		preg_match($last_click_pattern, $article_outer, $last_click_matches);
		$item['last_click'] = isset($last_click_matches[1]) ?  $last_click_matches[1] : "";
				
		$item['comment_count'] = $this->checkNestedElements($node, array('.js-comment-count'));
		$item['comment_count'] = $this->useAlternativeStructure($item['comment_count'], $node, array('.commentBubble'));


		$item['vote_count'] = $this->checkNestedElements($node, array('.js-vote-count'));
		$item['vote_count'] = $this->useAlternativeStructure($item['vote_count'], $node, array('.voting','.vote_count'));
		
		$item['success'] = $this->checkNestedElements($node, array('.success','.js-percent'));
		$item['success'] = $this->useAlternativeStructure($item['success'], $node, array('.voting', '.rating', '.percent'));
		$item['success'] = str_replace("<span>%</span>", "", $item['success']);
		
		$item['verified'] = $this->checkNestedElements($node, array('li.verified'));
		
		return $item;
	}
		
}

class DealnewsParser extends FileParser {

	private $hotness_menu_items;
	
	public function getHotnessMenuItems() {
		return $this->hotness_menu_items;
	}

	public function assignElementClass() {
		return ".article"; // ".article-block";
	}

	public function parseItem(simple_html_dom_node $node) {
		$item = array();
		
		$article_outer = $node->outertext;

		$item['article-outer'] = $article_outer; // for debugging only
		$item['title'] = $this->checkNestedElements($node, array('h3.headline-xlarge', 'a')); //$this->checkNestedElements($node, array('h3.article-heading', 'a.std-headline'));
		$item['details'] = $this->checkNestedElements($node, array('.article-body')); //$this->checkNestedElements($node, array('.article-detail'));


		//$item['links'][] = $title->find('a',0)->href;
		if ( is_array($node->find('a')) ) {
			foreach($node->find('a') as $link) {
				$item['links'][] = $link->href;
			}			
		}


		$tags = "";
		$category_path = $this->checkNestedElements($node, array('a[data-iref]'), "href"); //$this->checkNestedElements($node, array('a[data-iref=fp-category-3col]'), "href");
		if ($category_path) {
			$temp =  explode ( "/" , $category_path );
			foreach ($temp as $tag) {
				if (strlen($tag) > 0) {
					$tags[] = $tag;
				}
			}
		}
		
		$item['tags'] = $tags;
	
		$item['primary_image'] = $this->checkNestedElements($node,array(".media", ".art-image-large", "a", "img"),"src"); //$this->checkNestedElements($node,array(".article-specs", ".body", ".leftCol", "a", "img"),"src");
		if ($item['primary_image'] == "") {
			$comment = $this->checkNestedElements($node,array("comment"));
			$comment_inner = str_replace ( array("<!--", "-->"), "", $comment );
			$comment_object = str_get_html ($comment_inner);
//			$item['primary_image'] = $this->checkNestedElements($comment_object,array("img"),"src");
		}

		// Get "hotness" from the img title=hotness-level
		foreach ($node->find('img[title]') as $img) {
		// $debug .= "<div> img->title: " . $img->title . "</div>";
			if ( strstr ( $img->title , "hotness" ) ) {
				$item['hotness'] = $img->title;
				$hotness_class = preg_replace('/[^a-z0-9]/i', '-', $item['hotness']);
				$item['hotness_class'] = $hotness_class;
				$this->hotness_menu_items[$hotness_class] = $item['hotness'];
			}
		}
		if ( is_array($this->hotness_menu_items) ) { ksort ($this->hotness_menu_items ); }
		
		foreach($item as $key=>$html) {
			$item[$key] = $this->dealnewsFormat($html);
		}
		
		return $item;
	}
	
	private function dealnewsFormat($html) {
	// Strips out dealnews formatting

		if (!is_string($html)) {
			return $html;
		}
		
		$shd = new simple_html_dom();
		$shd->load($html);

		foreach($shd->find ( 'div' ) as $div) {
			$div->class = null;
		}
		
		foreach($shd->find('a') as $a) {
			$href = $a->href;
			$innertext = $a->innertext;
			$a->outertext = "<a" . " href='" . $href . "'>" . $innertext . "</a>";
		}
				
		$html = $shd->save(); // dumps DOM to string
		$shd->clear(); 
		unset($shd);
		return $html;
	}

		
}


?>