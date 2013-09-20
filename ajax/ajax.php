<?php
require_once("../config/local.config"); // define PROXY_IP | APP_ROOT_PATH | FILE_STORE_PATH
require_once("../config/urls-local.config");
require_once("../config/scraper-pages.config");

require_once("file-downloader-class.php");
require_once("file-parser-class.php");


class Client {
	private $url;
	private $rmnFileParser;
	private $fileStores;
	private $table;
	private $theadings;
	private $parsedContent;
	
	public function __construct($urls) {
		$this->urls = $urls;
	}
	
	public function downloadIt() {
		$this->pfd = new FileDownloader( $this->urls );
		$this->pfd->setAppRootPath(APP_ROOT_PATH);
		$this->pfd->setFileStorePath(FILE_STORE_PATH);
		$this->pfd->createCurlMultiHandler();
		$this->pfd->storeFiles();
		$this->fileStores = $this->pfd->getFileStores();
	}
	
	public function parseThem() {
		$this->parsedContent = array();
		foreach ($this->fileStores as $fileStore) {
			$key = pathinfo($fileStore['fileStore'], PATHINFO_BASENAME);
			$this->parsedContent[$key] = $this->parseIt($fileStore['fileStore']);
			
		}
	}

	public function parseIt($fileStore) {
		$this->rmnFileParser = FileParser::createFromFile("RetailmenotParser", $fileStore);
		$this->rmnFileParser->parseDomObject();
		
		$this->formatIt( $this->rmnFileParser->getParsedContent() );
		return $this->rmnFileParser->getParsedContent();
	}
	
	public function formatIt($array) {
		//foreach key in $array[0], popultate theadings
		$this->theadings = "";
		foreach (array_keys($array[0]) as $key) {
			$this->theadings .= "<th>" . $key . "</th>" . PHP_EOL;
		}

		
		//foreach ($array as $item), populate row of $this->table
		foreach ($array as $item) {
			$this->table .= "<tr style='vertical-align:top'>" . PHP_EOL;
			foreach ($item as $field) {
				$this->table .= "<td>" . $field . "</td>" . PHP_EOL;
			}
			$this->table .= "</tr>" . PHP_EOL;
		}
	}
	
	public function getParsedContent() {
		return $this->parsedContent;
	}
	
	public function getTable() {
		$thead = "<table border=1><thead>" . $this->theadings . "</thead><tbody>" . PHP_EOL;
		$tfoot = "</tbody><tfoot></tfoot></table>";
		$this->table = $thead . $this->table . $tfoot;
		return $this->table;
	}
	
}

set_time_limit(60);
$urls = $localTestUrls;
shuffle($urls);

$client = new Client($urls);
$client->downloadIt();

$client->parseThem();

if (isset($_GET['table'])) {
	echo "<html><body>" . $client->getTable() . "</body></html>";	
} else {
	echo json_encode( $client->getParsedContent() );	
}
//echo "<pre>" . print_r($client->parseThem(), TRUE) . "</pre>";
?>