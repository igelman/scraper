<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("../config/local.config");
require_once("pdo-manager-class.php");

class ClientSelectParsedContent {
	private $parsedContentArray;
	private $aggregateArray;
	private $table = "";
	
	public function queryParsedContent() {
		$dbh = PdoManager::getInstance();
		try {
			$stmt = $dbh->prepare("SELECT url, date_retrieved, parsed_content FROM files WHERE LENGTH(parsed_content) > 0");
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
	
	public function getParsedContentArray() {
		return $this->parsedContentArray;
	}
	
	public function aggregateParsedContent() {
		$this->aggregateArray = array();
		foreach($this->parsedContentArray as $pageRecord) {
			$url = $pageRecord['url'];
			$date_retrieved = $pageRecord['date_retrieved'];	
			$parsedContent = json_decode($pageRecord['parsed_content'], TRUE);
			foreach ($parsedContent as $item){
				$tr = "<tr class='item'>" . PHP_EOL;
				$tr .= "<td class='url'>" . $url . "</td>" . PHP_EOL;
				$tr .= "<td class='date_retrieved'>" . $date_retrieved . "</td>" .PHP_EOL;
				$tr .= "<td class='title'>" . $item['title'] . "</td>" .PHP_EOL;
				$tr .= "</tr>" . PHP_EOL;
				
				$this->table .= $tr;
			}
		}
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

$client = new ClientSelectParsedContent();
$client->queryParsedContent();
$client->aggregateParsedContent();

$client->getParsedContentArray();
$html = "<html><body>" . PHP_EOL . $client->getAggregateTable() . "</body></html>";
$writeLength = file_put_contents($fileStore, $html) ;

if ($writeLength) {
	exit( "*** I wrote $writeLength bytes to $fileStore" . PHP_EOL );
} else {
	die( "*** Something went wrong when I tried to write to $fileStore" . PHP_EOL);
}

?>