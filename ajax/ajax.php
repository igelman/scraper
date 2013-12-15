<?php
require_once("client-download-and-process-class.php");
require_once("xmlrpc-client-class.php");

$usageMessage = "Usage: post 'action' (and arguments as required for that action).";
if (!isset($_POST['action'])) die(json_encode($usageMessage));
switch ($_POST['action']) {
	case 'downloadAndProcess':
		downloadAndProcess();
		break;
	case 'listAllUrls':
		listAllUrls();
		break;
	case 'postCouponToTjd':
		postCouponToTjd();
		break;
	default:
		echo json_encode ($usageMessage);
}

function postCouponToTjd() {
	$username = "rpcxml";
	$password = "oT5VcsoF";
	$blogId = 0;
	$endpoint = "http://localhost/development/wordpress/xmlrpc.php";
	$xmlrpcClient = new XmlrpcClient($username, $password, $blogId, $endpoint);

	$encoding='UTF-8';
	$postTitle = htmlentities($_POST['postTitle',ENT_NOQUOTES,$encoding);
	$postContent = $_POST['postContent'];
	$postType = "tmt-coupon-posts";
	$customFields = array(
		array(
			"key" 	=> "code",
			"value"	=> $_POST['couponCode'],
		),
		array(
			"key"	=> "expires",
			"value"	=> $_POST['couponExpires'] // YYYYMMDD
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
	
	$taxonomies = array(
		"product_type"	=> $_POST['productTypes'],
		"merchant"	=> array($_POST['merchant']),
	);

    $postParams = $this->xmlrpcClient->createPostParams($postTitle, $postContent, $postType, $customFields, $taxonomies);
    $xmlrpcRequest = $this->xmlrpcClient->createRequest("wp.newPost", $postParams);
    $response = $this->xmlrpcClient->sendRequest();	
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