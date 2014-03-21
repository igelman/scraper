<?php
require_once(__DIR__ . "/curl-with-callback-class.php");
require_once(__DIR__ . "/semaphore-manager-class.php");
require_once(__DIR__ . "/pdo-manager-class.php");
require_once(__DIR__ . "/file-parser-class.php");



// Check command line arguments for --set
//  syntax: php rmn-agent.php --set=10
$options = getopt("", ["set::", "queued"]);
//var_dump($options);
$set = isset($options['set']) ? intval($options['set']) : NULL;
$message = PHP_EOL;
$message .= date("Y-m-d H:i:s") . PHP_EOL;

// Parameters for semaphore file. If it exists, this process exits.
// If this process fails because of a humanCheck, it creates the file.
$semaphoreDir = __DIR__ . "/../data/html/";
$semaphoreBase = "semaphore.flag";
$semaphoreContent = "Agent encountered a human check at RMN. Clear the captcha, and delete the file at " . $semaphoreDir . $semaphoreBase . PHP_EOL;
$semaphoreContent .= "Agent will stall until the file $semaphoreBase is removed." . PHP_EOL;

// If the semaphore already exists, halt execution.
$sm = new SemaphoreManager($semaphoreDir, $semaphoreBase);
if ( $sm->semaphoreExists() ) {
	$message .= $sm->readSemaphore();
	logMessage($message);
	exit();
}

if (isset($set)) {
	$message .= "#SELECT url from set " . $set . PHP_EOL;
	$url = selectStalestUrl($set);
} elseif (isset($options['queued'])) {
	$message .= "#SELECT queued url" . PHP_EOL;
	$url = selectStalestUrl(NULL, TRUE);
} else {
	// Choose the oldest record. 
	$url = selectStalestUrl();
}

if (!isset($url)) {
	$message .= "#Nothing to curl" . PHP_EOL;
	exit($message);
}

// Curl it
$message .= "#UPDATING MERCHANT Curling $url..." . PHP_EOL;
$cc = new CurlWithCallback($url);
$cc->executeCurl();
$ch = $cc->getCurlHandle();
$html = $cc->getCurlResult();

// Bail if curl failed
if (strlen($html) == 0) {
	$message .= "#UPDATE FAILED Curl failed. strlen(\$html) = " . strlen($html) . PHP_EOL;
	logMessage($message);
	exit($message);
}

// Process it
if (handleHumanCheck($ch, $sm)) {
	exit("We got found out: $url" . PHP_EOL);
}
$url = handleNewUrl($url, $ch);
$parsedContent = parseContent($html);

if (strlen($parsedContent) > 5) {
	updateRecord($url, $html, $parsedContent);	
} else {
	$message .= "#UPDATE FAILED Parse failed. strlen(\$parsedContent) = " . strlen($parsedContent) . PHP_EOL;
}

logMessage($message);

function logMessage($message) {
	$logDir = __DIR__ . "/../data/html/";
	$logBase = "rmn-agent.log";
	file_put_contents($logDir . $logBase, date("Y-m-d H:i:s") . PHP_EOL . $message . PHP_EOL . "*****" . PHP_EOL, FILE_APPEND);
	echo $message;
}

function handleNewUrl($url, $ch) {
	// if effectiveurl != url, update the DB with the new url, log change
	global $message;
	$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	if ( $effectiveUrl != $url ) {
		$message .= "#NEW URL $url is now $effectiveUrl." . PHP_EOL;
		$dbh = PdoManager::getInstance();
		try {
			$stmt = $dbh->prepare("UPDATE files SET url=:effectiveUrl WHERE url=:url");
			$stmt->bindParam(':effectiveUrl', $effectiveUrl);
			$stmt->bindParam(':url', $url);

			if($stmt->execute()) {
				$message .= "#UPDATE URL Updated " . $stmt->rowCount() . " row. SET url=$effectiveUrl WHERE url=$url." . PHP_EOL;
				return $effectiveUrl;
			} else {
				$message .= "#UPDATE URL Update failed: SET url=$effectiveUrl WHERE url=$url." . PHP_EOL;
				return FALSE;
			}
		}
		catch(PDOException $e){
			$message .= "#UPDATE URL Update failed: SET url=$effectiveUrl WHERE url=$url." . PHP_EOL;
			$message .= $e->getMessage() . PHP_EOL;
			return FALSE;
		}
	}
	return $url;
}

function handleHumanCheck($ch, $sm) {
	// if presented with humanCheck, log, notify, and exit
	global $message;

	$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	if (strstr($effectiveUrl, "humanCheck") ) {
		$path = $sm->getPath();
		$message .= "#HUMANCHECK Presented with human check at $effectiveUrl ." . PHP_EOL;
		$message .= "#HUMANCHECK Agent encountered a human check at RMN. Clear the captcha, and delete the file at $path ." . PHP_EOL;
		$message .= "#HUMANCHECK Agent will stall until the file $semaphoreBase is removed." . PHP_EOL;

		$sm->createSemaphore();
		$sm->setContent($message);
		$sm->sendSemaphoreContents("alan@igelman.com", "alert from rmn-agent");
		return TRUE;
	}
	return FALSE;
}

function parseContent($html) {
	// Send curl result to FileParser
	$rmnParser = FileParser::createFromHtml("RetailmenotParser", $html);
	$rmnParser->parseDomObject();
	return json_encode($rmnParser->getParsedContent());
}

function updateRecord($url, $html, $parsedContent) {
	global $message;
	$dbh = PdoManager::getInstance();
	$dateRetrieved = date("Y-m-d H:i:s");
	try {
		$stmt = $dbh->prepare("UPDATE files SET content=:html, date_retrieved = :dateRetrieved, parsed_content = :parsed_content, queued = FALSE WHERE url=:url");
		$stmt->bindParam(':html', $html);
		$stmt->bindParam(':dateRetrieved', $dateRetrieved);
		$stmt->bindParam(':url', $url);
		$stmt->bindParam(':parsed_content', $parsedContent);
		
		if($stmt->execute()) {
			$message .= "#UPDATED RECORD Updated " . $stmt->rowCount() . " row." . PHP_EOL;
			return TRUE;
		} else {
			$message .= "#UPDATED RECORD Didn't update $url with blob." . PHP_EOL . $stmt->errorInfo . PHP_EOL;
			return FALSE;
		}
	}
	catch(PDOException $e){
		$message .= $e->getMessage() . PHP_EOL;
		return FALSE;
	}
}

function selectStalestUrl($set=NULL, $queued=FALSE) {
	global $message;
	
	$where  = "";
	if ($set || $queued) {
		$where = "WHERE date_retrieved < NOW() - INTERVAL 1 DAY";
		$where .= $set ? " AND set_number = :set" : "";
		$where .= $queued ? " AND queued = :queued" : "";
		$message .= "#where clause: " . $where . PHP_EOL;
	}
	
//	$whereSet = $set ? "WHERE set_number = :set AND date_retrieved < NOW() - INTERVAL 1 DAY" : "";
	$select = "SELECT url FROM files " . $where . " ORDER BY date_retrieved asc LIMIT 1";
	
	$dbh = PdoManager::getInstance();
	try {
		$stmt = $dbh->prepare($select);
		if (isset($set)) {
			$stmt->bindValue(':set', (int) $set, PDO::PARAM_INT);	
		}
		if (isset($queued)) {
			$stmt->bindValue(':queued', $queued, PDO::PARAM_STR);	
		}
		$stmt->execute();
		$row = $stmt->fetch();
		return $row['url'];
	} catch(PDOException $e){
		$message .= "#SELECT MERCHANT selectStalestUrl failed ... " . $e->getMessage() . PHP_EOL;
		logMessage("", "", $message);
		exit($message);
	}
}

?>