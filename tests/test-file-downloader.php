<?php
include("../ajax/file-downloader-class.php");

class testFileDownloader extends PHPUnit_Framework_TestCase {

	private $fd;
	private $urls;
	
	protected function setUp(){
		$this->urls = array(
			"http://localhost/development/scraper/data/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gap-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gap-20130904-1140.html",
		);
		$this->fd = new FileDownloader($this->urls);
	}

	public function testConstruct() {
		$this->assertInstanceOf('FileDownloader',$this->fd);
	}
	
	public function testCleanUrlArray() {
		$this->urls = $this->fd->cleanUrlArray($this->urls);	// remove duplicate items
		$this->assertEquals(count($this->urls), 3); // of which there are 3 in the 6 item array
	}
}

?>

