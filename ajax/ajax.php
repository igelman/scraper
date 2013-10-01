<?php
require_once("client-download-and-process-class.php");

$usageMessage = "Usage: post 'action' (and arguments as required for that action).";
if (!isset($_POST['action'])) die(json_encode($usageMessage));
switch ($_POST['action']) {
	case 'downloadAndProcess':
		downloadAndProcess();
		break;
	case 'listAllUrls':
		listAllUrls();
		break;
	default:
		echo json_encode ($usageMessage);
}

function downloadAndProcess(){
	if (isset($_POST['urls']) && isset($_POST['element-id'])) {
		$urlsClient = new ClientDownloadAndProcessUrls($_POST['urls']);
		$return['package'] = $urlsClient->processUrls();
		$return['post'] = $_POST;
	} /*
else (isset($_POST['setNumber'])) {
		$setClient = new ClientDownloadAndProcessSet($_POST['setNumber']);
		$setClient->selectUrls();
		echo $setClient->processUrls();
	}
*/ else {
		$return['message'] = "Usage: post either 'urls' (array of urls) or 'setNumber' (integer set id).";
	}
	
	echo json_encode($return);
	
}

function listAllUrls() {
	$pm = PdoManager::getInstance();
	try {
		$stmt = $pm->prepare("SELECT date_retrieved, set_number, url FROM files");
	
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute();
		
		$result = array();
		foreach($stmt as $row) {
			$result[] = $row;
		}
		echo json_encode($result);

	} catch(PDOException $e) {
		echo $e->getMessage();
	}

}

?>