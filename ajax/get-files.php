<?php 
/**
Input:
	array of file urls
	local target directory
Side effect:
	copy each file from soure URL to target directory
Output:
	path to file
	size of file
**/

$debug = "";

// Get urls
include_once("../config/scraper-pages.config"); // $urls is initialized in the config
$urls = cleanUrlArray($urls); // de-dupe and die if no valid urls
$debug .= "urls:<pre>" . print_r($urls, TRUE) . "</pre>";

// Create curl opts
$defaultOptions = createCurlOptionsArray(); // create the default curl options.
$debug .= "defaultOptions:<pre>" . print_r($defaultOptions, TRUE) . "</pre>";

// Set up a curl multi handle
$mh = createCurlMultiHandler($urls, $defaultOptions);
$debug .= "<div>mh:<pre>" . print_r($mh,TRUE) . "</pre></div>";


// Execute curl & write files
// Initial execution
/*
$active = null;
do {
	$debug .= "<div>entering do";
	$mrc = curl_multi_exec($mh, $active)
	$debug .= "finishing do</div>";
} while ($mrc == CURLM_CALL_MULTI_PERFORM );



// Main loop
while ($active && $mrc == CURLM_OK) {
	if (curl_multi_select($mh) != -1) {
		do {
			$mrc = curl_multi_exec($mh, $active)
		} while ($mrc == CURLM_CALL_MULTI_PERFORM );		
	}
	if ($mhinfo = curl_multi_info_read($mh)) { // If one of the requests finished
		$chinfo = curl_getinfo($mhinfo['handle']);
		// Do some stuff with the result
		// Clean up
		curl_multi_remove_handle($mh, $mhinfo['handle']);
		curl_close($mhinfo['handle']);
		
	}
}
*/


// Finish
curl_multi_close($mh);



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
**/
	$mh = curl_multi_init();
	foreach ($urls as $url) {
		$options = $defaultOptions;
		$options[CURLOPT_URL] = $url; // add the url to curl options (other options are constant) 
		$GLOBALS['debug'] .= "<div>url:" . $url . "<pre>" . print_r($options,TRUE) . "</pre></div>";
	
		// set the handler for ths url
		
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$GLOBALS['debug'] .= "<div>ch:<pre>" . print_r($ch,TRUE) . "</pre></div>";
		
		// add the handle to the multi-handler
		curl_multi_add_handle($mh, $ch); 
	}
	return $mh;
} // createCurlMultiHandler

?>