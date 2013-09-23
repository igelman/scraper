<?php
include("../ajax/file-downloader-class.php");
include_once("../config/scraper.config");

class testFileDownloader extends PHPUnit_Framework_TestCase {

	private $fd;
	private $urls;
	private $uniqueUrls;
	private $testFilesInfo;
	private $proxyIp;
	
	protected function setUp(){
	
		$this->proxyIp = "127.0.0.1:9150";
		$this->urls = array(
			//"http://www.retailmenot.com/view/gamefly.com",
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gap-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gap-20130904-1140.html",
		);
		$this->proxyUrls = array("http://localhost/development/scraper/tests/sample-files/gap-20130904-1140.html",);
		$this->files = array(
			"sample-files/gamefly-20130904-1140.html",
			"sample-files/gamestop-20130904-1140.html",
			"sample-files/gap-20130904-1140.html",
			"sample-files/gamefly-20130904-1140.html",
			"sample-files/gamestop-20130904-1140.html",
			"sample-files/gap-20130904-1140.html",
		);
		$this->uniqueUrls = count(array_unique($this->urls)); // 3, i.e., number of unique urls in $urls
		$this->uniqueProxyUrls = count(array_unique($this->proxyUrls));
		
		$appRootPath = $GLOBALS['appRootPath'];
		$fileStorePath = $GLOBALS['fileStorePath'];		

		foreach($this->files as $file) {
			$name = pathinfo($file, PATHINFO_BASENAME);
			$this->testFilesInfo[$name] = filesize($file);
		}
		//print_r($this->testFilesInfo);
		
		$this->fd = new FileDownloader($this->urls);
		$this->fd->setAppRootPath($appRootPath);
		$this->fd->setFileStorePath($fileStorePath);
		
		$this->pfd = new ProxyFileDownloader($this->proxyUrls, $this->proxyIp);
		$this->pfd->setAppRootPath($appRootPath);
		$this->pfd->setFileStorePath($fileStorePath);
		
		// delete all files in fileStorePath
		$files = glob($appRootPath . $fileStorePath . "*"); // get all file names
		foreach($files as $file){ // iterate files
			if(is_file($file))
				unlink($file); // delete file
		}
	}

	public function testConstruct() {
		$this->assertInstanceOf('FileDownloader',$this->fd);
		$this->assertEquals($this->uniqueUrls, count($this->fd->getUrls())); // constructor de-dupes array, so 3 unique items in the 6 item array
	}

	public function testProxyConstruct() {
		$this->assertInstanceOf('ProxyFileDownloader',$this->pfd);
		$this->assertEquals($this->uniqueProxyUrls, count($this->pfd->getUrls())); // constructor de-dupes array, so 3 unique items in the 6 item array
		$this->assertArrayHasKey(CURLOPT_PROXY, $this->pfd->curlOptsArray);
		$this->assertEquals($this->pfd->curlOptsArray[CURLOPT_PROXY], $this->proxyIp);

	}

	
	public function testSetExtraCurlOptions(){
		$this->fd->setExtraCurlOptions(array(CURLOPT_DNS_USE_GLOBAL_CACHE=>TRUE,CURLOPT_HTTPGET=>TRUE));
/*  Commenting out these assertions, since there's no need to make this variable public (or to provide a getter)
		$this->assertArrayHasKey(CURLOPT_DNS_USE_GLOBAL_CACHE, $this->fd->curlOptsArray);
		$this->assertArrayHasKey(CURLOPT_HTTPGET, $this->fd->curlOptsArray);
*/
		return;
	}
	
	public function testCreateCurlMultiHandler() {
		$this->fd->createCurlMultiHandler();
		$this->assertEquals("curl_multi", get_resource_type($this->fd->mh) );
		$this->assertEquals($this->uniqueUrls, count($this->fd->curlHandlers));
		
		$this->pfd->createCurlMultiHandler();
		$this->assertEquals("curl_multi", get_resource_type($this->pfd->mh) );
		$this->assertEquals($this->uniqueProxyUrls, count($this->pfd->curlHandlers));

	}
	
	public function testWriteFile(){
		$string = "this is a string";
		$fileStore = $GLOBALS['appRootPath'] . $GLOBALS['fileStorePath'] . time() . "-" . "testWriteFile.txt" ;
		$lengthOfWrite = $this->fd->writeFile($fileStore, $string);
		$this->assertEquals(strlen($string), $lengthOfWrite);
		
		$writtenFileContents = file_get_contents($fileStore);
		$this->assertEquals($writtenFileContents, $string);
		
	}
	
	public function testStoreFiles() {
		$this->fd->createCurlMultiHandler();
		$this->fd->storeFiles();
		$this->assertEquals("curl_multi", get_resource_type($this->fd->mh) );
		$this->assertEquals($this->uniqueUrls, count($this->fd->getFileStores() ));
				
		foreach($this->fd->getFileStores() as $file) {
//$testFilesInfo['name'] = pathinfo($file, PATHINFO_BASENAME);
//$testFilesInfo['size'] = filesize($file);
			$name = pathinfo($file['url'], PATHINFO_BASENAME);
			$size = filesize($file['fileStore']);
			$expectedSize = $this->testFilesInfo[$name];
			//echo $name . " " . $expectedSize . $size . PHP_EOL . PHP_EOL;
			$this->assertEquals($expectedSize, $size );


			//print_r($file);
			//$testFilesInfo['name'] = pathinfo($file, PATHINFO_BASENAME);
			//$testFilesInfo['size'] = filesize($file);
		}		
		//echo json_encode ( $this->fd->getFileStores(), TRUE );
		
		$this->pfd->createCurlMultiHandler();
		$this->pfd->storeFiles();
		$fileStores = $this->pfd->getFileStores();
		$this->assertTrue( filesize( $fileStores[0]['fileStore'] ) > 0);
		echo print_r ( $this->pfd->getFileStores() );

	}
	
	public function testStoreFilesToDb() {
		echo "*** testStoreFilesToDb ***" . PHP_EOL;
		$urls = array("http://www.retailmenot.com/view/adidas.com");
		$fd2db = new FiletoBlobDownloader($urls);
		$this->assertInstanceOf('FileToBlobDownloader',$fd2db);
		$fd2db->createCurlMultiHandler();
		$this->assertEquals("curl_multi", get_resource_type($fd2db->mh) );
		$fd2db->storeFiles();

	}
	
	
}

?>

