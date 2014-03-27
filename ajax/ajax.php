<?php
require_once("pdo-manager-class.php");
require_once("client-download-and-process-class.php");
require_once("xmlrpc-client-class.php");
require_once("client-select-parsed-content-class.php");
require_once("../config/local.config");

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
	
	case 'NEWpostToTjd':
		NEWpostToTjd();
		break;
	case 'fetchAllUrls':
		fetchAllUrls();
		break;
	case 'addToSet':
		addToSet();
		break;
	case 'addUrlToQueue':
		addUrlToQueue();
		break;
	case 'addSetToQueue':
		addSetToQueue();
		break;
	default:
		echo json_encode ($usageMessage);
}

function addSetToQueue() {
	$pm = PdoManager::getInstance();
	$set = intval($_POST['set']);
	try {
		$stmt = $pm->prepare("UPDATE files SET queued = TRUE WHERE set_number = :set");
		$stmt->bindValue(':set', $set, PDO::PARAM_INT);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute();

	} catch(PDOException $e) {
		echo $e->getMessage();
	}
	$return['rowCount'] = $stmt->rowCount();
	$return['post'] = $_POST;
	echo json_encode($return);
}


function addUrlToQueue() {
	$pm = PdoManager::getInstance();
	$url = $_GET['url'];
	try {
		$stmt = $pm->prepare("UPDATE files SET queued = TRUE WHERE url = :url");
		$stmt->bindValue(':url', $url, PDO::PARAM_STR);
		$stmt->execute();
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
	$return['rowCount'] = $stmt->rowCount();
	$return['get'] = $_GET;
	echo json_encode($return);
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

/**
 * Checks whether a string is valid json.
 *
 * @param string $string
 * @return boolean
 */
function isJson($string) {
    try {
        // try to decode string
        json_decode($string);
    }
    catch (ErrorException $e) {
        // exception has been caught which means argument wasn't a string and thus is definitely no json.
        return FALSE;
    }

    // check if error occured
    return (json_last_error() == JSON_ERROR_NONE);
}

function pushToArrayIfPosted($customFieldsArray, $customFieldName, $postKey, $date = FALSE) {
	if (isset($_POST[$postKey])) {
		// If the post value is a date, format it Ymd
		$value = $date && strtotime($_POST[$postKey]) ? date("Ymd", strtotime($_POST[$postKey])) : $_POST[$postKey];
		array_push($customFieldsArray, [
			"key" 	=> $customFieldName,
			"value"	=> json_decode($value),
		]);
	}
	return $customFieldsArray;
}

function OLDpostToTjd() {
	$username = RPCXML_USER;
	$password = RPCXML_PASS;
	$blogId = 0;
	$endpoint = "http://localhost/development/wordpress/xmlrpc.php";
	$xmlrpcClient = new XmlrpcClient($username, $password, $blogId, $endpoint);

	$encoding='UTF-8';
	$postTitle = htmlentities($_POST['postTitle'],ENT_NOQUOTES,$encoding);
	$postContent = $_POST['postContent'];
	$postType = $_POST['postType']; //"tmt-coupon-posts";
	
	$customFields = [];
	$customFields = pushToArrayIfPosted($customFields, "code", "couponCode");
	$customFields = pushToArrayIfPosted($customFields, "expires", "couponExpires", TRUE); // This also needs a strtotime($couponExpires) ? date("Ymd", strtotime($couponExpires))
	$customFields = pushToArrayIfPosted($customFields, "url", "couponUrl");
	$customFields = pushToArrayIfPosted($customFields, "offer_id", "postOfferId");
	
	$productTypes = isset($_POST['productTypes']) ? $_POST['productTypes'] : NULL;
	$merchant = isset($_POST['merchant']) ? $_POST['merchant'] : NULL;
	$taxonomies = array(
		"product_type"	=> json_decode($productTypes),
		"merchant"	=> json_decode($merchant),
	);

    $postParams = $xmlrpcClient->createPostParams($postTitle, $postContent, $postType, $customFields, $taxonomies);
    $xmlrpcRequest = $xmlrpcClient->createRequest("wp.newPost", $postParams);
    $response = $xmlrpcClient->sendRequest();
    
    $return['request'] = $xmlrpcRequest;
    $return['response'] = $response;
    $return['post'] = $_POST;
    $return['element-id'] = $_POST['element-id'];
    
    $return['postParams'] = $postParams;
    
    echo json_encode($return);
}

function wordpressConfig() {
	return [
		"username"	=> RPCXML_USER,
		"password"	=> RPCXML_PASS,
		"blogId"	=> 0,
		"endpoint"	=> "http://localhost/development/wordpress/xmlrpc.php",
	];
}

function postToTjd() {
	$wordpressConfig = wordpressConfig();
	$xmlrpcClient = new XmlrpcClient($wordpressConfig['username'], $wordpressConfig['password'], $wordpressConfig['blogId'], $wordpressConfig['endpoint']);
	$encoding='UTF-8';

	$postTitle = htmlentities($_POST['postTitle'],ENT_NOQUOTES,$encoding);
	$postContent = $_POST['postContent'];
	$postType = $_POST['postType']; //"tmt-coupon-posts";

	$customFieldsArray = [];
	
	if (isset($_POST['customFields']) && count($_POST['customFields']) > 0) {
		foreach ( ($_POST['customFields']) as $customFieldName => $customFieldValue ) {
			array_push($customFieldsArray, [
				"key" 	=> $customFieldName,
				"value"	=> json_decode($customFieldValue),
			]);
		}		
	}
	
	$taxonomiesArray = isset($_POST['taxonomies']) ? $_POST['taxonomies'] : NULL ;
/*
	$taxonomiesArray = [];
	foreach ($taxonomies as $taxonomyName => $taxonomyTerms) {
		array_push($taxonomiesArray, [
			$taxonomyName	=> ($taxonomyTerms),
		]);
	}
*/

    $postParams = $xmlrpcClient->createPostParams($postTitle, $postContent, $postType, $customFieldsArray, $taxonomiesArray);
    $xmlrpcRequest = $xmlrpcClient->createRequest("wp.newPost", $postParams);
    $response = $xmlrpcClient->sendRequest();
    
    $return['request'] = $xmlrpcRequest;
    $return['response'] = $response;
    $return['element-id'] = $_POST['element-id'];
    $return['post'] = $_POST;
    
    $return['postParams'] = $postParams;

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