<?php
include("../ajax/file-downloader-class.php");

class testFileDownloader extends PHPUnit_Framework_TestCase {

	private $fd;
	private $urls;
	private $uniqueUrls;
	
	protected function setUp(){
		$this->urls = array(
			"http://localhost/development/scraper/data/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gap-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/data/sample-files/gap-20130904-1140.html",
		);
		$this->uniqueUrls = count(array_unique($this->urls)); // 3, i.e., number of unique urls in $urls
		$this->fd = new FileDownloader($this->urls);
	}

	public function testConstruct() {
		$this->assertInstanceOf('FileDownloader',$this->fd);
		$this->assertEquals($this->uniqueUrls, count($this->fd->getUrls())); // constructor de-dupes array, so 3 unique items in the 6 item array
	}
	
	
	public function testCreateCurlMultiHandler() {
		$this->fd->createCurlMultiHandler();
		$this->assertEquals("curl_multi", get_resource_type($this->fd->mh) );
		$this->assertEquals($this->uniqueUrls, count($this->fd->curlHandlers));
	}
	
	public function testStoreFiles() {
		$this->fd->createCurlMultiHandler();
		$this->fd->storeFiles();
		$this->assertEquals("curl_multi", get_resource_type($this->fd->mh) );
	}
}

?>

