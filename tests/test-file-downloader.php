<?php
require_once("../ajax/file-downloader-class.php");
require_once("../ajax/file-parser-class.php");
require_once("../config/scraper.config");

class testFileDownloader extends PHPUnit_Framework_TestCase {

	private $fd;
	private $localUrls; // locally hosted files so our tests don't attract attention
	private $proxyUrls;
	private $uniqueUrls;
	private $uniqueProxyUrls;
	private $testFilesInfo;
	private $proxyIp;
	private $appRootPath;
	private $fileStorePath;
	
	protected function setUp(){
	
		$this->proxyIp = "127.0.0.1:9150";
		$this->localUrls =array(
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130923-1854.html",
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130923-1854.html",
			"http://localhost/development/scraper/tests/sample-files/1800flowers-20130923-1200.html",
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130923-1854.html",
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130923-1854.html",
			"http://localhost/development/scraper/tests/sample-files/1800flowers-20130923-1200.html",
		);
		$this->proxyUrls = array("http://localhost/development/scraper/tests/sample-files/1800flowers-20130923-1200.html",);
		$this->files = array(
			"sample-files/gamefly-20130923-1854.html",
			"sample-files/1800flowers-20130923-1200.html",
		);
		$this->uniqueUrls = count(array_unique($this->localUrls)); // 2, i.e., number of unique urls in $urls
		$this->uniqueProxyUrls = count(array_unique($this->proxyUrls));
		
		$this->appRootPath = $GLOBALS['appRootPath'];
		$this->fileStorePath = $GLOBALS['fileStorePath'];		

		foreach($this->files as $file) {
			$name = pathinfo($file, PATHINFO_BASENAME);
			$this->testFilesInfo[$name] = filesize($file);
		}
		
/*
After refactoring, these methods can be moved to a general function library
		$this->fd = new FileDownloader($this->urls);
		$this->fd->setAppRootPath($appRootPath);
		$this->fd->setFileStorePath($fileStorePath);
*/
		
		$this->fd = new FileDownloaderWithCallback($this->localUrls);
		
		$this->pfd = new ProxyFileDownloader($this->proxyUrls, $this->proxyIp);
/*
		$this->pfd->setAppRootPath($appRootPath);
		$this->pfd->setFileStorePath($fileStorePath);
*/
		
		// delete all files in fileStorePath
		$files = glob($this->appRootPath . $this->fileStorePath . "*"); // get all filenames in directory
		foreach($files as $file){
			if(is_file($file)) unlink($file); // delete each file
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
		
/*
	public function testWriteFile(){
		$string = "this is a string";
		$fileStore = $GLOBALS['appRootPath'] . $GLOBALS['fileStorePath'] . time() . "-" . "testWriteFile.txt" ;
		$lengthOfWrite = $this->fd->writeFile($fileStore, $string);
		$this->assertEquals(strlen($string), $lengthOfWrite);
		
		$writtenFileContents = file_get_contents($fileStore);
		$this->assertEquals($writtenFileContents, $string);		
	}
*/
	
/*
	public function testStoreFiles() {
		$this->fd->createCurlMultiHandler();
		$this->fd->storeFiles();
		$this->assertEquals("curl_multi", get_resource_type($this->fd->mh) );
		$this->assertEquals($this->uniqueUrls, count($this->fd->getFileStores() ));
				
		foreach($this->fd->getFileStores() as $file) {
			$name = pathinfo($file['url'], PATHINFO_BASENAME);
			$size = filesize($file['fileStore']);
			$expectedSize = $this->testFilesInfo[$name];
			$this->assertEquals($expectedSize, $size );

		}		

		
		$this->pfd->createCurlMultiHandler();
		$this->pfd->storeFiles();
		$fileStores = $this->pfd->getFileStores();
		$this->assertTrue( filesize( $fileStores[0]['fileStore'] ) > 0, "Make sure proxy server is running");
		echo print_r ( $this->pfd->getFileStores() );

	}
*/

	public function testCallbackToFile() {
		echo PHP_EOL . "*** testCallbackToFile ***" . PHP_EOL;
		
		$urls = $this->localUrls;
		$uniqueUrls = count(array_unique($urls)); // 2, i.e., number of unique urls in $urls

		$fdwc = new FileDownloaderWithCallback($urls);		
		$this->assertEquals($uniqueUrls, count($fdwc->getUrls())); // constructor de-dupes array, so 2 unique items in the 6 item array

		$fdwc->createCurlMultiHandler();

		$callback = function($ch){
			$appRootPath = $GLOBALS['appRootPath'];
			$fileStorePath = $GLOBALS['fileStorePath'];		

			$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			$urlBasename = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_BASENAME );
			$fileStore = $appRootPath . $fileStorePath . time() . "-" . $urlBasename ;

			// Get from url
			if ( ($html = curl_multi_getcontent($ch) ) === FALSE){ // test length of retrieved file
				$error = curl_error($ch);
			}

			// Put to file
			$length = file_put_contents($fileStore, $html);
			if ( ($length) === FALSE) { // test length of written file
				echo "crap" . PHP_EOL;
			}
			return array(
				'url'		=>	$url,
				'curlInfo'	=>	curl_getinfo($ch),
				'fileStore'	=>	$fileStore,
			);
		};

		$ecReturn = $fdwc->executeCurls($callback);
/*
		echo PHP_EOL . "testCallbackToFile ecReturn: " . PHP_EOL;
		echo print_r($ecReturn, TRUE);
*/
		$writeFilesSize0 = filesize($ecReturn[0]['fileStore']);
		$readFileSize0 = $ecReturn[0]['curlInfo']['size_download'];
		$this->assertEquals($writeFilesSize0, $readFileSize0, "assertEquals(\$writeFilesSize0, \$readFileSize0)");

	}
	
	public function testCallbackToDb() {
		echo PHP_EOL . "*** testCallbackToDb ***" . PHP_EOL;

		$fdwc = new FileDownloaderWithCallback(array("http://www.retailmenot.com/view/aveda.com",));//"http://localhost/development/scraper/tests/sample-files/gamefly-20130923-1854.html"));
		$this->assertInstanceOf('FileDownloaderWithCallback', $fdwc,"assertInstanceOf('FileDownloaderWithCallback', \$fdwc)");
		$this->assertInstanceOf('FileDownloader', $fdwc, "assertInstanceOf('FileDownloader', \$fdwc)");

		$fdwc->createCurlMultiHandler();
		$this->assertEquals('curl_multi', get_resource_type($fdwc->mh), "assertEquals('curl_multi', get_resource_type(\$fdwc->mh) )" );
		
/*
		$callback = function($ch){
			return $ch;
		};
*/
		$callback = function($ch){
			$return = "";
			$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			$downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
			if ( ($html = curl_multi_getcontent($ch) ) === FALSE){
				$error = curl_error($ch);
			}

			$rmnParser = FileParser::createFromHtml("RetailmenotParser", $html);
			$rmnParser->parseDomObject();
			$parsed_content = json_encode($rmnParser->getParsedContent());
			$dbh = PdoManager::getInstance();
			try {
				$stmt = $dbh->prepare("UPDATE files SET content=:html, date_retrieved = NOW(), parsed_content = :parsed_content WHERE url=:url");
				$stmt->bindParam(':html', $html);
				$stmt->bindParam(':url', $url);
				$stmt->bindParam(':parsed_content', $parsed_content);
				
				if($stmt->execute()) {
					$return .= "Update $url with blob size $downloadSize" . PHP_EOL;
				} else {
					$return .= "Didn't update $url with blob" . PHP_EOL;
					$return .= "UPDATE files SET content=html WHERE url=$url" . PHP_EOL . PHP_EOL;
				}
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
			return $return;

		};

		$ecReturn = $fdwc->executeCurls($callback);
//		$this->assertTrue($ecReturn, "assertTrue(ecReturn) means callback fired");
		echo PHP_EOL . "testCallbackToDb ecReturn: " . PHP_EOL;
		echo print_r($ecReturn, TRUE);

	}
	
	public function testNoCallback() {
		echo PHP_EOL . "*** testNoCallback ***" . PHP_EOL;

		$fdwc = new FileDownloaderWithCallback($this->localUrls); //array("http://localhost/development/scraper/tests/sample-files/gamefly-20130923-1854.html"));
		$this->assertInstanceOf('FileDownloaderWithCallback', $fdwc,"assertInstanceOf('FileDownloaderWithCallback', \$fdwc)");
		$this->assertInstanceOf('FileDownloader', $fdwc, "assertInstanceOf('FileDownloader', \$fdwc)");

		$fdwc->createCurlMultiHandler();
		$this->assertEquals('curl_multi', get_resource_type($fdwc->mh), "assertEquals('curl_multi', get_resource_type(\$fdwc->mh) )" );
		
		$ecReturn = $fdwc->executeCurls();
//		$this->assertFalse($ecReturn, "tested assertNull(ecReturn)");	
		echo PHP_EOL . "testNoCallback ecReturn: " . PHP_EOL;
		echo print_r($ecReturn, TRUE);

	}
}

?>

