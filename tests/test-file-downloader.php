<?php
require_once("../ajax/file-downloader-class.php");
require_once("../ajax/file-parser-class.php");
require_once("../config/scraper.config");

class TestFileDownloader extends PHPUnit_Framework_TestCase {

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
				
		$this->fd = new FileDownloader($this->localUrls);
		$this->pfd = new ProxyFileDownloader($this->proxyUrls, $this->proxyIp);
		$this->fdwc = new FileDownloader($this->localUrls);
		
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
	
	public function testEmptyUrlArray() {
		$urls = array();
		$emptyFd = new FileDownloader($urls);
	}

	public function testProxyConstruct() {
		$this->assertInstanceOf('ProxyFileDownloader',$this->pfd);
		$this->assertEquals($this->uniqueProxyUrls, count($this->pfd->getUrls())); // constructor de-dupes array, so 3 unique items in the 6 item array
		$this->assertArrayHasKey(CURLOPT_PROXY, $this->pfd->curlOptsArray);
		$this->assertEquals($this->proxyIp, $this->pfd->curlOptsArray[CURLOPT_PROXY]);

	}

/*
	public function testCallbackToFile() {
		echo PHP_EOL . "*** testCallbackToFile ***" . PHP_EOL;
		
		$urls = $this->localUrls;
		$uniqueUrls = count(array_unique($urls)); // 2, i.e., number of unique urls in $urls

//		$fdwc = new FileDownloaderWithCallback($urls);
		$fdwc = new FileDownloader($urls);
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
		$writeFilesSize0 = filesize($ecReturn[0]['fileStore']);
		$readFileSize0 = $ecReturn[0]['curlInfo']['size_download'];
		$this->assertEquals($writeFilesSize0, $readFileSize0, "assertEquals(\$writeFilesSize0, \$readFileSize0)");

	}
*/
	
/*
	public function testCallbackToDb() {
		echo PHP_EOL . "*** testCallbackToDb ***" . PHP_EOL;

		$fdwc = new FileDownloader(array("http://www.retailmenot.com/view/adidas.com",));//"http://localhost/development/scraper/tests/sample-files/gamefly-20130923-1854.html"));
		$this->assertInstanceOf('FileDownloader', $fdwc, "assertInstanceOf('FileDownloader', \$fdwc)");

		$fdwc->createCurlMultiHandler();
		$this->assertEquals('curl_multi', get_resource_type($fdwc->mh), "assertEquals('curl_multi', get_resource_type(\$fdwc->mh) )" );

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
		echo PHP_EOL . "testCallbackToDb ecReturn: " . PHP_EOL;
		echo print_r($ecReturn, TRUE);
	}
*/
	
	public function testCreateCurlHandlersArray() {
		$this->fdwc->createCurlMultiHandler();
		$curlHandlers = $this->fdwc->getCurlHandlers();
		$this->assertInternalType("array", $curlHandlers);
		$this->assertEquals('curl',get_resource_type($curlHandlers[0]));
	}
	
	public function testCurlDownloadsTheFile() {
		$this->fdwc->createCurlMultiHandler();
		$this->fdwc->executeCurls();
		$curlHandlers = $this->fdwc->getCurlHandlers();
		$ch = $curlHandlers[0];
		$curlInfo = curl_getinfo($ch);
		$file = "sample-files/gamefly-20130923-1854.html";//$this->localUrls[0];
		$this->assertTrue($curlInfo['size_download'] == filesize($file), "assetTrue that curlInfo[size_download] = filesize(file)");
	}
	
	public function testCallbackReturnsCallbackResult() {
		echo PHP_EOL . "*** testCallback ***" . PHP_EOL;

		$callback=function($ch){
			$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			$downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
			return array(
				'finalUrl'			=> $finalUrl,
				'downloadSize'	=>	$downloadSize,
			);
		};

		$this->fdwc->createCurlMultiHandler();
		$this->fdwc->executeCurls($callback);
		$ecReturn = $this->fdwc->getCallbackReturn();
		echo PHP_EOL . "testCallbackReturnsCallbackResult ecReturn: " . PHP_EOL;
		echo print_r($ecReturn, TRUE);
		echo PHP_EOL . "sizeof(ecReturn): " . sizeof($ecReturn) . PHP_EOL;
		echo "this->uniqueUrls: $this->uniqueUrls" . PHP_EOL;
		$this->assertEquals(sizeof($ecReturn), $this->uniqueUrls, "assertEquals(sizeof(\$ecReturn), \$this->uniqueUrls");
		
	}
	
	public function testNoCallbackReturnsEmptyArray() {
		echo PHP_EOL . "*** testNoCallback ***" . PHP_EOL;

		$this->fdwc->createCurlMultiHandler();
		$this->fdwc->executeCurls();
		$ecReturn = $this->fdwc->getCallbackReturn();
		echo PHP_EOL . "testNoCallbackReturnsEmptyArray ecReturn: " . PHP_EOL;
		echo print_r($ecReturn, TRUE);
		$this->assertTrue((sizeof($ecReturn)==0), "assertTrue(sizeof(\ecReturn)==0");
	}
	
	public function testSleepParameterInsertsPauseInCurlRequests() {
		echo PHP_EOL . "*** testSleepParameterInsertsPauseInCurlRequests ***" . PHP_EOL;
	
		$sleep = 30;
		$callback=function($ch){
			return new DateTime();
		};

		$fdwcs = new FileDownloader($this->localUrls, $sleep);
		$fdwcs->createCurlMultiHandler();
		$fdwcs->executeCurls($callback);
		$ecReturn = $fdwcs->getCallbackReturn();
		$interval = $ecReturn[0]->diff($ecReturn[1]);
		$secondsInterval = $interval->format('%s');
		echo "secondsInterval: ". print_r($secondsInterval, TRUE) . PHP_EOL;
		$this->assertTrue($secondsInterval >= $sleep/2 && $secondsInterval <= $sleep*3/2, "secondsInterval should be between 1/2 and 3/2 of \$sleep");
	
	}



}


?>

