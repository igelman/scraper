<?php
require_once("../ajax/curl-with-callback-class.php");
require_once("../ajax/semaphore-manager-class.php");
require_once("pdo-manager-class.php");
require_once("file-parser-class.php");


$semaphoreDir = "../data/html/"; // "/Users/alantest/Downloads/";
$semaphoreBase = "semaphore.flag";

$message = "";

// If the semaphore already exists, halt execution.
$sm = new SemaphoreManager($semaphoreDir, $semaphoreBase);
if ( $sm->semaphoreExists() ) {
	exit($message . $sm->readSemaphore());
}

$url = selectStalestUrl(); //"http://igelman.com/development/humanCheck.html";
$message .= $url . PHP_EOL;
$callback = createCallbackFunctionToUpdateDb();

$cc = new CurlWithCallback($url, $callback);
$message .= "Curling $url..." . PHP_EOL;
$cc->executeCurl();
var_dump ($cc->getCallbackReturn());

function selectStalestUrl() {
	$dbh = PdoManager::getInstance();
	try {
		$stmt = $dbh->prepare("SELECT url FROM files ORDER BY date_retrieved asc LIMIT 1");
		$stmt->execute();
		$row = $stmt->fetch();
		return $row['url'];
	} catch(PDOException $e){
		exit ( $e->getMessage() );
	}
}

function createCallbackFunctionToUpdateDb() {
	return function($ch, $html) {
		$semaphoreDir = "../data/html/"; // "/Users/alantest/Downloads/";
		$semaphoreBase = "semaphore.flag";
		$semaphoreContent = "Agent encountered a human check at RMN. Clear the captcha, and delete the file at " . $semaphoreDir . $semaphoreBase . PHP_EOL;
		$semaphoreContent .= "Agent will stall until the file $semaphoreBase is removed." . PHP_EOL;

		$return = "";
		$effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		if (strstr($effectiveUrl, "humanCheck") ) {
			$sm = new SemaphoreManager($semaphoreDir, $semaphoreBase, $content = $semaphoreContent);
			$sm->createSemaphore();
			$sm->sendSemaphoreContents("alan@igelman.com", "alert from rmn-agent");
			exit("We got found out: $effectiveUrl" . PHP_EOL);
		}
		$downloadSize = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
		if ( ($html ) === FALSE){
			$error = curl_error($ch);
		}
		$dateRetrieved = date("Y-m-d H:i:s");
		
		// Send curl result to FileParser
		$rmnParser = FileParser::createFromHtml("RetailmenotParser", $html);
		$rmnParser->parseDomObject();
		$parsed_content = json_encode($rmnParser->getParsedContent());
		
		// Update files table with the parsed content
		$dbh = PdoManager::getInstance();
		try {
			$stmt = $dbh->prepare("UPDATE files SET content=:html, date_retrieved = :dateRetrieved, parsed_content = :parsed_content WHERE url=:url");
			$stmt->bindParam(':html', $html);
			$stmt->bindParam(':dateRetrieved', $dateRetrieved);
			$stmt->bindParam(':url', $effectiveUrl); // no good if there's a redirect. Use the original url, not the curl result.
			$stmt->bindParam(':parsed_content', $parsed_content);
			
			if($stmt->execute()) {
				$return['CURLINFO_EFFECTIVE_URL'] = $effectiveUrl;
				$return['size'] = $downloadSize;
				$return['time'] = $dateRetrieved;
				$return['message'] .= "Update $effectiveUrl with blob size $downloadSize" . PHP_EOL;
				//$return['message'] .= print_r(curl_getinfo($ch), TRUE);
			} else {
				$return['message'] .= "Didn't update $effectiveUrl with blob" . PHP_EOL;
				$return['message'] .= "UPDATE files SET content=html WHERE url=$effectiveUrl" . PHP_EOL . PHP_EOL;
			}
		}
		catch(PDOException $e){
			$return['message'] .= $e->getMessage();
		}

		return $return;
	};
}

?>