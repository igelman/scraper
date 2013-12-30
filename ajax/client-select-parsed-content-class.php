<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("../config/local.config");
require_once("pdo-manager-class.php");

class ClientSelectParsedContent {
	private $parsedContentArray;
	private $aggregateArray;
	private $table = "";
	private $offset;
	private $maxRecords;
	private $urls;
	private $dbh;
	private $stmt;
	public $url;
	
	public function __construct($offset = NULL, $maxRecords = NULL) {
		$this->offset = (isset($offset)) ? $offset : 0;
		$this->maxRecords = (isset($maxRecords)) ? ($maxRecords) : 999999;
		$this->urls = (isset($_POST['urls'])) ? $_POST['urls'] : null;
	}
	
	public function createQuery() {
		$this->queryString = "SELECT url, date_retrieved, parsed_content FROM files WHERE LENGTH(parsed_content) > 0 " . $this->createWhereUrlClause($this->url) . " LIMIT :maxRecords OFFSET :offset";
	
		$this->dbh = PdoManager::getInstance();
		$this->stmt = $this->dbh->prepare($this->queryString);
	}
	
	public function createWhereUrlClause($url=NULL) {
		if (isset($url)) {
			$this->url = $url;
			return "AND url = :url";	
		}
		return "";
	}
	
	public function getQueryStatement() {
		return $this->stmt;
	}
	
	public function bindParameters() {
		$return = FALSE;

		$return = $this->stmt->bindValue(':offset', (int) $this->offset, PDO::PARAM_INT);
		$return = $return && $this->stmt->bindValue(':maxRecords', (int) $this->maxRecords, PDO::PARAM_INT);
		
		if (isset($this->url)) {
			$return = $return && $this->stmt->bindValue(':url', $this->url, PDO::PARAM_STR);
		}

		return $return; 
	}
	
	public function executeQuery() {
		try {
			$this->stmt->setFetchMode(PDO::FETCH_ASSOC);
			$this->stmt->execute();	
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public function getParsedContent() {
		$this->parsedContentArray = array();
		foreach($this->stmt as $row) {
			$this->parsedContentArray[] = array(
				'url'	=> $row['url'],
				'date_retrieved'	=> $row['date_retrieved'],
				'parsed_content'	=> $row['parsed_content'],
			);
		}
		return $this->parsedContentArray;
	}
	
	public function queryParsedContent() {
		$dbh = PdoManager::getInstance();
		try {			
			$stmt = $dbh->prepare("SELECT url, date_retrieved, parsed_content FROM files WHERE LENGTH(parsed_content) > 0 LIMIT :maxRecords OFFSET :offset");

			$offset = $this->offset;
			$maxRecords = $this->maxRecords;

			$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
			$stmt->bindValue(':maxRecords', (int) $maxRecords, PDO::PARAM_INT);

			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->execute();
			
			$this->parsedContentArray = array();
			foreach($stmt as $row) {
				$this->parsedContentArray[] = array(
					'url'	=> $row['url'],
					'date_retrieved'	=> $row['date_retrieved'],
					'parsed_content'	=> $row['parsed_content'],
				);
			}
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
	
/*
	private function makeInUrlSqlPhrase() {
		$inUrlSqlPhrase = (isset($this->urls)) ? "AND url IN (" . implode(',', $_POST['urls']) . ")" : "";
		return $inUrlSqlPhrase;
	}
*/
	
	public function getParsedContentArray() {
		return $this->parsedContentArray;
	}
	
/*
	public function setOffset($offset){
		$this->offset = $offset;
	}
	
	public function setMaxRecords($maxRecords) {
		$this->maxRecords = $maxRecords;
	}
*/
	
	public function aggregateParsedContent() {
		$this->aggregateArray = array();
		foreach($this->parsedContentArray as $pageRecord) {
			$sourceUrl = $pageRecord['url'];
			$dateRetrieved = $pageRecord['date_retrieved'];	
			$parsedContent = json_decode($pageRecord['parsed_content'], TRUE);
			foreach ($parsedContent as $item){
				$item['source_url'] = $sourceUrl;
				$item['date_retrieved'] = $dateRetrieved;
				$this->aggregateArray[] = $item;
			}
		}
	}
	
	public function getAggregateArray(){
		return $this->aggregateArray;
	}
	
	public function getAggregateTable(){
		return "<table><tbody>" . PHP_EOL . $this->table . PHP_EOL . "</tbody></table>";
	}
}

/**
*
*/

/*
$appRootPath = APP_ROOT_PATH; // $GLOBALS['appRootPath'];
$fileStorePath = FILE_STORE_PATH; //$GLOBALS['fileStorePath'];		

$fileStore = $appRootPath . $fileStorePath . time() . "-table.html" ;

$offset = isset($_GET['offset']) ? $_GET['offset'] : NULL;
$maxRecords = isset($_GET['maxRecords']) ? $_GET['maxRecords'] : NULL;
$client = new ClientSelectParsedContent($offset, $maxRecords);
$client->queryParsedContent();
$client->aggregateParsedContent();

$client->getParsedContentArray();
echo json_encode($client->getAggregateArray()) . PHP_EOL;
*/

?>