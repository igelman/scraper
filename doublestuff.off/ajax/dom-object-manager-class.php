<?php
require_once("simplehtmldom/simple_html_dom.php");

/**
* DomObjectManager creates and disposes of DOM object (using simple_html_dom library).
* Subclasses accept html string or file path as parameter 
*/
class DomObjectManager {
	protected $domObject;
	
	protected function __construct() {
		$this->domObject = new simple_html_dom();
	}
	
	public function getDomObject(){
		return $this->domObject;
	}

	public function clearObject ($object) {
	// clear and unset object
		if (is_object($object)) {
			$object->clear();	
		}
		unset($object);
	} // clearObject ($object)

	
}

/**
* Create a domObject from html content
* @param $html string representing html content
*/
class HtmlDomObjectManager extends DomObjectManager {

	public function __construct($html) {
		parent::__construct();
		$this->domObject->load($html);
	}
}

/**
* Create a domObject from either a local file or a url
* @param $file string representing path/to/file or url
*/
class FileDomObjectManager extends DomObjectManager {

	public function __construct($file) {
		parent::__construct();
		$this->domObject->load_file($file);
	}
}

?>