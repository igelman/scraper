<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("../config/local.config");
require_once("pdo-manager-class.php");
require_once("file-parser-class.php");
require_once("file-downloader-class.php");

$pm = PdoManager::getInstance();


$setNumber = 67;
try {
	$stmt = $pm->prepare("SELECT url FROM files WHERE set_number = :set_number");
	$stmt->bindParam(':set_number', $setNumber);

	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	
	$urls = array();
	foreach($stmt as $row) {
		$urls[] = $row['url'];
		
/*
		$rmnParser = FileParser::createFromHtml("RetailmenotParser", $row['content']);
		$rmnParser->parseDomObject();
		$parsedContent[$row['url']] = $rmnParser->getParsedContent();

*/


	}
	$fd2db = new FiletoBlobDownloader($urls);
	$fd2db->createCurlMultiHandler();
	$fd2db->storeFiles();

	
} catch(PDOException $e) {
	echo $e->getMessage();
}





?>