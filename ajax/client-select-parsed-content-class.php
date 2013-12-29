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
	
	public function __construct($offset = NULL, $maxRecords = NULL) {
			$this->offset = (isset($offset)) ? $offset : 0;
			$this->maxRecords = (isset($maxRecords)) ? ($maxRecords) : 5;
			$this->urls = (isset($_POST['urls'])) ? $_POST['urls'] : null;
		
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
	
	private function makeInUrlSqlPhrase() {
		$inUrlSqlPhrase = (isset($this->urls)) ? "AND url IN (" . implode(',', $_POST['urls']) . ")" : "";
		return $inUrlSqlPhrase;
	}
	
	public function getParsedContentArray() {
		return $this->parsedContentArray;
	}
	
	private function handlePostData() {
		
	}
	
	public function setOffset($offset){
		$this->offset = $offset;
	}
	
	public function setMaxRecords($maxRecords) {
		$this->maxRecords = $maxRecords;
	}
	
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
/*
				$tr = "<tr class='item'>" . PHP_EOL;
				$tr .= "<td class='url'>" . $url . "</td>" . PHP_EOL;
				$tr .= "<td class='date_retrieved'>" . $date_retrieved . "</td>" .PHP_EOL;
				$tr .= "<td class='title'>" . $item['title'] . "</td>" .PHP_EOL;
				$tr .= "</tr>" . PHP_EOL;
				
				$this->table .= $tr;
*/
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

?>