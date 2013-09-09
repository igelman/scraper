<?php 
/**
Curl a list of URLs

Input:
	array of file urls
	local target directory
Side effect:
	copy each file from soure URL to target directory
Output:
	path to file
	size of file

Documentation:
	http://www.phpied.com/simultaneuos-http-requests-in-php-with-curl/
	http://net.tutsplus.com/tutorials/php/techniques-and-resources-for-mastering-curl/
**/

$debug = "";

// Get urls ////////////////////////////
include_once("../config/scraper-pages.config"); // $urls is initialized in the config
$urls = cleanUrlArray($urls); // de-dupe and die if no valid urls
$debug .= "urls:<pre>" . print_r($urls, TRUE) . "</pre>";
////////////////////////////////////////

// Create curl opts ////////////////////
$defaultOptions = createCurlOptionsArray(); // create the default curl options.
$debug .= "defaultOptions:<pre>" . print_r($defaultOptions, TRUE) . "</pre>";
////////////////////////////////////////

// Set up a curl multi handle //////////
$handlers = createCurlMultiHandler($urls, $defaultOptions);
$mh = $handlers['mh'];
$chs = $handlers['chs'];
$debug .= "<div>mh:<pre>" . print_r($mh,TRUE) . "</pre></div>";
////////////////////////////////////////

// Execute curl & write files //////////
$running = null;
do {
	curl_multi_exec($mh, $running);
} while($running > 0);
////////////////////////////////////////

// get content and remove handles //////
foreach($chs as $id => $ch) {
/*
	$result[$id] = curl_multi_getcontent($ch);
	if ($result[$id] === FALSE){ // check for empty output
		$error = curl_error($ch);
	}
	$debug .= "<div>id $id length:" . strlen( $result[$id] ) . "</div>";
*/
	$debug .= "<div>id $id length:" . handleCurlOutput($ch) . "</div>";
	curl_multi_remove_handle($mh, $ch);
}
////////////////////////////////////////

// Finish //////////////////////////////
curl_multi_close($mh);
////////////////////////////////////////


/*
$output = curl_exec($ch);
curl_close($ch);
if ($output === FALSE){ // check for empty output
	$error = curl_error($ch);
}
$info = curl_getinfo($ch);
*/
	/*
	url
	content_type
	http_code
	header_size
	request_size
	filetime
	ssl_verify_result
	redirect_count
	total_time
	namelookup_time
	connect_time
	pretransfer_time
	size_upload
	size_download
	speed_download
	speed_upload
	download_content_length
	upload_content_length
	starttransfer_time
	redirect_time
	*/

// Return output
echo $debug;

function cleanUrlArray($urlArray) {
	$urlArray = array_values(array_unique($urlArray)); // de-dupe the array
	// add a check to make sure these are all valid urls
	if (!$urlArray) die('No URL to check');
	return $urlArray;
}

function createCurlOptionsArray() {
	$userAgent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)";
	$defaultOptions = array(
		CURLOPT_RETURNTRANSFER => TRUE, // return content
		CURLOPT_USERAGENT      => $userAgent, // set user-agent
		CURLOPT_AUTOREFERER    => TRUE,
		CURLOPT_FOLLOWLOCATION => TRUE, // follow redirects
		CURLOPT_URL            => "", // this is where we'll put the target url
	);
	return $defaultOptions;
} // createCurlOptionsArray

function createCurlMultiHandler($urls, $defaultOptions) {
/**
Input:
	$urls array of urls
	$defaultOptions default set of curl options
Output:
	$mh curl multi-handler
	$ch[] array of curl handlers
**/
	$i = 0;
	$mh = curl_multi_init();
	foreach ($urls as $url) {
		$options = $defaultOptions;
		$options[CURLOPT_URL] = $url; // add the url to curl options (other options are constant) 
		$GLOBALS['debug'] .= "<div>url:" . $url . "<pre>" . print_r($options,TRUE) . "</pre></div>";
	
		// set the handler for ths url
		
		$ch[$i] = curl_init();
		curl_setopt_array($ch[$i], $options);
		$GLOBALS['debug'] .= "<div>ch[$i]:<pre>" . print_r($ch[$i],TRUE) . "</pre></div>";
		
		// add the handle to the multi-handler
		curl_multi_add_handle($mh, $ch[$i]);
		$i++;
	}
	return array("mh" => $mh, "chs" => $ch);
} // createCurlMultiHandler

function handleCurlOutput($ch) {
/**
Input:
	$ch current curl handler
Side effect:
	Write curl output to local file
	Handle error if no output
Output:
	$result of current curl handler
**/

	$webRootPath = "/Volumes/Sulhee iMac HD/Library/Server/Web/Data/Sites/Default/";
	$appRootPath = "development/scraper/";	// path relative web root
	$fileStorePath = "data/html/";		// path relative to app root


	$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	$url_basename = pathinfo( parse_url( $url, PHP_URL_PATH ), PATHINFO_BASENAME );
	$fileStore = $webRootPath . $appRootPath . $fileStorePath . $url_basename ;
	
	$GLOBALS['debug'] .= "<div><li>url: $url</li><li>url_basename: $url_basename</li><li>fileStore: $fileStore</li></div>";
	
	
	if ( ($html = curl_multi_getcontent($ch) ) === FALSE){ // check for empty output
		$error = curl_error($ch);
	}
	
	if ( ($length = file_put_contents($fileStore, $html) ) === FALSE) {
		return "crap";
	}
	return $length; // returns the number of bytes that were written to the file, or FALSE on failure

}

function storeFile($html, $fileStorePath) {
/**
Store html file
Input:
	$html string of html
Side effect:
	Write string to local file
	Handle error if no output
Output:
	$file path to local file
**/

	return;
} // storeFile

?>