<?php
require_once("pdo-manager-class.php");
require_once("client-download-and-process-class.php");
require_once("xmlrpc-client-class.php");
require_once("client-select-parsed-content-class.php");

$usageMessage = "Usage: post (or get) 'action' (and arguments as required for that action).";
if (!isset($_POST['action']) && !isset($_GET['action'])) {
	die(json_encode($usageMessage));
}
if (isset($_GET['action'])) {
	$action = $_GET['action'];
}
if (isset($_POST['action'])) {
	$action = $_POST['action'];
}

switch ($action) {
	case 'downloadAndProcess':
		downloadAndProcess();
		break;
	case 'listAllUrls':
		listAllUrls();
		break;
	case 'listCoupons':
		listCoupons();
		break;
	case 'postToTjd':
		postToTjd();
		break;
	case 'fetchAllUrls':
		fetchAllUrls();
		break;
	case 'addToSet':
		addToSet();
		break;
	default:
		echo json_encode ($usageMessage);
}

function addToSet() {
	$pm = PdoManager::getInstance();
	$set = $_GET['set'];
	$url = $_GET['url'];
	try {
		$stmt = $pm->prepare("UPDATE files SET set_number = :set WHERE url = :url");
		$stmt->bindValue(':set', $set, PDO::PARAM_INT);
		$stmt->bindValue(':url', $url, PDO::PARAM_STR);
		$stmt->execute();
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
	
	$return['rowCount'] = $stmt->rowCount();
	$return['get'] = $_GET;
	echo json_encode($return);
}

function fetchAllUrls() {
	$pm = PdoManager::getInstance();
	try {
		$stmt = $pm->prepare("SELECT url FROM files WHERE LENGTH(parsed_content) > 0");
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute();
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
	$result = array();
	foreach($stmt as $row) {
		$result[] = $row;
	}
	echo json_encode($result);
}

function listCoupons() {
	$offset = isset($_GET['offset']) ? $_GET['offset'] : NULL;
	$maxRecords = isset($_GET['maxRecords']) ? $_GET['maxRecords'] : NULL;
	$url = isset($_GET['url']) ? $_GET['url'] : NULL;

	$client = new ClientSelectParsedContent($offset, $maxRecords, $url);
	$client->createQuery();
	$client->bindParameters();
	$client->executeQuery();
	$client->getParsedContent();
	$client->aggregateParsedContent();
	echo json_encode($client->getAggregateArray());
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

function postToTjd() {
	$username = "rpcxml";
	$password = "oT5VcsoF";
	$blogId = 0;
	$endpoint = "http://localhost/development/wordpress/xmlrpc.php";
	$xmlrpcClient = new XmlrpcClient($username, $password, $blogId, $endpoint);

	$encoding='UTF-8';
	$postTitle = htmlentities($_POST['postTitle'],ENT_NOQUOTES,$encoding);
	$postContent = $_POST['postContent'];
	$postType = $_POST['postType']; //"tmt-coupon-posts";
	$customFields = array(
		array(
			"key" 	=> "code",
			"value"	=> $_POST['couponCode'],
		),
		array(
			"key"	=> "expires",
			"value"	=> strtotime($_POST['couponExpires']) ? date("Ymd", strtotime($_POST['couponExpires'])) : ""  // YYYYMMDD
		),
		array(
			"key"	=>	"url",
			"value"	=> $_POST['couponUrl'],
		),
		array(
			"key"	=> "offer_id",
			"value"	=> $_POST['postOfferId'],
		),
	);
	
	$productTypes = isset($_POST['productTypes']) ? $_POST['productTypes'] : NULL;
	$merchant = isset($_POST['merchant']) ? $_POST['merchant'] : NULL;
	$taxonomies = array(
		"product_type"	=> json_decode($productTypes),
		"merchant"	=> array($merchant),
	);

    $postParams = $xmlrpcClient->createPostParams($postTitle, $postContent, $postType, $customFields, $taxonomies);
    $xmlrpcRequest = $xmlrpcClient->createRequest("wp.newPost", $postParams);
    $response = $xmlrpcClient->sendRequest();
    
    $return['request'] = $xmlrpcRequest;
    $return['response'] = $response;
    $return['post'] = $_POST;
    $return['element-id'] = $_POST['element-id'];
    
    echo json_encode($return);
}

function downloadAndProcess(){
	$return = [];
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

?>