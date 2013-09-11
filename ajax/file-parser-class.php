<?php

require_once("../assets/php/simplehtmldom/simple_html_dom.php");

class FileParser {

	private $domObject;
	
	private function __construct() {}
	
	static function create($subclass) {
		return new $subclass();
	}
	
	public function createDomObject($html) {
		unset ($this->domObject);
		$this->domObject = new simple_html_dom();
		$this->domObject->load($html);
/*
		$this->domObject = new simple_html_dom();
		$this->domObject->load($html);
*/
	}

	public function clearObject ($object) {
	// clear and unset object
		if (is_object($object)) {
			$object->clear();	
		}
		unset($object);
	} // clearObject ($object)


}

class RetailmenotParser extends FileParser {
	
}

class DealnewsParser extends FileParser {
	
}

?>