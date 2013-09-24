<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

require_once("../config/local.config");
require_once("pdo-manager-class.php");
require_once("file-parser-class.php");

$pm = PdoManager::getInstance();

$url = "http://www.retailmenot.com/view/apple.com";

try {
	$stmt = $pm->prepare("SELECT content FROM files WHERE url = :url");
	$stmt->bindParam(':url', $url);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$content = $stmt->fetchColumn();
} catch(PDOException $e) {
	echo $e->getMessage();
}

$rmnParser = FileParser::createFromHtml("RetailmenotParser", $content);


?>