<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("../config/local.config");
require_once("pdo-manager-class.php");
require_once("file-parser-class.php");

$pm = PdoManager::getInstance();

try {
//	$stmt = $pm->prepare("SELECT url, content FROM files WHERE date_retrieved > SUBDATE( NOW(), 7 )");
	$stmt = $pm->prepare("SELECT url, content FROM files WHERE url = :url");
	$stmt->bindParam(':url', $url);
	$url = "http://www.retailmenot.com/view/1800flowers.com";

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	
	$rmnParser = array();
	foreach($stmt as $row) {
		$rmnParser = FileParser::createFromHtml("RetailmenotParser", $row['content']);
		$rmnParser->parseDomObject();
		$parsedContent[$row['url']] = $rmnParser->getParsedContent();
	}
	
} catch(PDOException $e) {
	echo $e->getMessage();
}

echo json_encode($parsedContent);
/*
$rmnParser = FileParser::createFromHtml("RetailmenotParser", $content);
$rmnParser->parseDomObject();
echo json_encode( $rmnParser->getParsedContent() );
echo $content;
*/

?>