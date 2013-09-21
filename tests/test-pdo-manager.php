<?php
// require_once("../ajax/pdo-manager-class.php");
require_once("../ajax/array-queuer-class.php");
require_once("../config/local.config"); // define DB connection params DB_HOST DB DB_USER DB_PASS
require_once("../config/urls-local.config"); // define $urls

$dbHost = DB_HOST;
$db = DB;
$dbUser = DB_USER;
$dbPass = DB_PASS;

try {
	$dbh = new PDO("mysql:host=$dbHost;dbname=$db", $dbUser, $dbPass, array(
		PDO::ATTR_PERSISTENT => true
	));

	
	$aq = new ArrayQueuer(selectUrlsFromFilesTable($dbh), 10);
	print_r($aq->getSets());
	
}
catch(PDOException $e){
	echo $e->getMessage();
}

function populateFilesTable($urls, $dbh){
//	$stmt = $dbh->prepare("INSERT INTO table (url) VALUES (:url) ON DUPLICATE KEY UPDATE url");
	$stmt = $dbh->prepare("INSERT INTO files (url) VALUES (:url)");
	$stmt->bindParam(':url', $url);
	
	foreach ($urls as $url) {
		if ($stmt->execute()) {
			echo "Inserted $url" . PHP_EOL;
		} else {
			echo "Didn't insert $url" . PHP_EOL;
		}	
	}
}

function selectUrlsFromFilesTable($dbh){
	$stmt = $dbh->query("SELECT url FROM files");
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$result = array();
	while($r = $stmt->fetchColumn()){
		$result[] = $r;
	}
	return $result;

}

?>