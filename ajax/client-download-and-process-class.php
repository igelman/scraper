<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("../config/local.config");
require_once("pdo-manager-class.php");
require_once("file-parser-class.php");
require_once("file-downloader-class.php");


class ClientDownloadAndProcess {
	private $setNumber;
	private $urls;

	public function __construct($setNumber) {
		$this->setNumber = $setNumber;
	}
	
	public function selectUrls() {
		$pm = PdoManager::getInstance();
		try {
			$stmt = $pm->prepare("SELECT url FROM files WHERE set_number = :set_number");
			$stmt->bindParam(':set_number', $this->setNumber);
		
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->execute();
			
			$this->urls = array();
			foreach($stmt as $row) {
				$this->urls[] = $row['url'];
			}
		} catch(PDOException $e) {
			echo $e->getMessage();
		}	
	}
	
	public function getUrls() {
		return $this->urls;
	}
	
	public function processUrls() {
		$fd = new FileDownloader($this->urls);
		$fd->createCurlMultiHandler();

		$callback = function($ch){
			$return = "";
			$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			$downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
			if ( ($html = curl_multi_getcontent($ch) ) === FALSE){
				$error = curl_error($ch);
			}

			echo PHP_EOL . "processUrls downloading $url ..." . PHP_EOL;

			$rmnParser = FileParser::createFromHtml("RetailmenotParser", $html);
			$rmnParser->parseDomObject();
			$parsed_content = json_encode($rmnParser->getParsedContent());
			$dbh = PdoManager::getInstance();
			try {
				$stmt = $dbh->prepare("UPDATE files SET content=:html, date_retrieved = NOW(), parsed_content = :parsed_content WHERE url=:url");
				$stmt->bindParam(':html', $html);
				$stmt->bindParam(':url', $url); // no good if there's a redirect. Use the original url, not the curl result.
				$stmt->bindParam(':parsed_content', $parsed_content);
				
				if($stmt->execute()) {
					$return .= "Update $url with blob size $downloadSize" . PHP_EOL;
					//$return .= print_r(curl_getinfo($ch));
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

		$ecReturn = $fd->executeCurls($callback);
		return $ecReturn;
		
	}
}

class ClientDownloadAndProcessSet extends ClientDownloadAndProcess {
	
}

class ClientDownloadAndProcessUrls extends ClientDownloadAndProcess {
	
}


/**
* Usage: "php client-class.php <int setNumber>"
*/
$setNumber = (isset($argv[1]) && $argc==2 ) ? $argv[1] : die("Usage: 'php client-class.php <int setNumber>'" . PHP_EOL);
$client = new ClientDownloadAndProcess($setNumber);
$client->selectUrls();
echo $client->processUrls();
echo "All done!" . PHP_EOL;

?>