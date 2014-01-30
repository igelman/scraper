<?php


require_once ("dom-object-manager-class.php");
require_once("file-parser-class.php");

$logfile = "ajax-dev.log";
$debug = FALSE;
$function = $_GET['function'];

switch ($function) {
	case 'Parse';
		$output = AjaxParse();
		//logToFile($logfile, "Parse ajax");
		echo $output;
		break;
	case 'CopyImage'; // 
		$output = CopyImage();
		//logToFile($logfile, "CopyImage ajax $output");
		echo $output;
		break;
	case 'findEndpoint'; // returns JSON { endpoint: i: }
		$url = $_GET['url'];
		$i = $_GET['i'];
//			$output = findEndpoint($url);
		$output_array = array(
			'endpoint' => findEndpoint($url),
			'i' => $i
		);
		$output = json_encode($output_array);
		//logToFile($logfile, "findEndpoint ajax " . $output);
		echo $output;
		break;
	case 'SkimLinkShortener'; // returns JSON {shorturl: status: error:}
		$output = SkimLinkShortener();
		logToFile($logfile, "SkimLinkShorterner ajax $output");
		echo $output;
		break;

}

function AjaxParse() {
	$source = $_GET['source'];
	$deal_source = $_GET['dealSource'];
	
	$output['message'] = "In AjaxParse()" . PHP_EOL;
	
	if ($deal_source == "dealnews") {
		$output['message'] .= "deal_source is $deal_source. About to call parserClient($source)" . PHP_EOL;
		$dealnewsParse = parserClient($source); // dealnewsParse ($source); // array('hotness_menu_items' => $hotness_menu_items,'items' => $items,'items_json' => $items_json)
		$output['hotness_menu_items'] = $dealnewsParse['hotness_menu_items'];
		$output['items'] = $dealnewsParse['items'];
		$output['message'] .= $dealnewsParse['message'];
	} else if ($deal_source == "rmn") {
		$rmnParse = retailmenotParse ($source);
		$output['staff_pick_menu_items'] = $rmnParse['staff_pick_menu_items'];
		$output['items'] = $rmnParse['items'];
	}
	return json_encode($output);
}

function parserClient($dnFile) {
	$return['message'] = "Called parserClient($dnFile)" . PHP_EOL;
	$dnFileParser = FileParser::createFromFile("DealnewsParser", $dnFile);
	$dnFileParser->parseDomObject();
	$return['items'] = $dnFileParser->getParsedContent();
	$return['items_json'] = json_encode($return['items']);
	$return['hotness_menu_items'] = $dnFileParser->getHotnessMenuItems();
	return $return;
}

function CopyImage () {
// Copy remote $url to $path
// http://www.php.net/manual/en/curl.examples-basic.php
	$url = $_GET['url'];
	$path = $_GET['path'];

	$ch = curl_init("$url");
	$fp = fopen("$path", "w");
	
	curl_setopt($ch, CURLOPT_FILE, $fp); // redirects output ($url) to file ($fp) instead of stdout
	curl_setopt($ch, CURLOPT_HEADER, 0);
	
	$output['status'] = curl_exec($ch) ? 200 : 400;
	curl_close($ch);
	fclose($fp);
	
	$output['fileinfo'] = imageType($path);
	$output['path'] = $output['fileinfo']['path'];
	$output['basename'] = basename($output['path']);

	return json_encode($output); // need new url + some kind of success/fail handler here and encode to JSON

}

function imageType($path) {
// Make sure that the image has the correct file extension
// (Notably, dealnews.com's images don't have file extenstions)

	$pathinfo = pathinfo($path); // get the file extension and other info
	$return_value['extension'] = $pathinfo['extension'];

	$return_value['image_type'] = exif_imagetype($path); // get the image type	
	switch ($return_value['image_type']) {
		case IMAGETYPE_GIF:
			$required_extension = 'gif';
			break;
		case IMAGETYPE_JPEG:
			$required_extension = 'jpg';
			break;
		case IMAGETYPE_PNG:
			$required_extension = 'png';
			break;
		case IMAGETYPE_BMP:
			$required_extension = 'bmp';
			break;
		default:
			return;
			break;
	}
	$return_value['requiredextension'] = $required_extension;

	// Fix the extenstion if it's missing or doesn't match the file type
	$return_value['path'] = $path;
	if (is_null($pathinfo['extension'])) {
		$new_path = $path . "." . $required_extension;
		$return_value['path'] = ( rename ( $path, $new_path) ) ? $new_path : $path;
	} elseif ( $pathinfo['extension'] != $required_extension) {
		$new_path = $pathinfo['dirname'] . $pathinfo['filename'] . "." . $required_extension;
		$return_value['path'] = ( rename ( $path, $new_path) ) ? $new_path : $path;
	}
	

	return $return_value;
/*
1	IMAGETYPE_GIF
2	IMAGETYPE_JPEG
3	IMAGETYPE_PNG
4	IMAGETYPE_SWF
5	IMAGETYPE_PSD
6	IMAGETYPE_BMP
7	IMAGETYPE_TIFF_II (intel byte order)
8	IMAGETYPE_TIFF_MM (motorola byte order)
9	IMAGETYPE_JPC
10	IMAGETYPE_JP2
11	IMAGETYPE_JPX
12	IMAGETYPE_JB2
13	IMAGETYPE_SWC
14	IMAGETYPE_IFF
15	IMAGETYPE_WBMP
16	IMAGETYPE_XBM
17	IMAGETYPE_ICO
*/
}
function findEndpoint ($url) {
	$logfile = $GLOBALS['logfile'];

	logToFile($logfile, "findEndpoint() about to loop on " . substr( $url , 0 , 90 ));
	while ($url) {
		$redirect = CheckRedirect($url);
		logToFile($logfile, "CheckRedirect returns: " . $redirect);
		if ( !is_null($redirect) ) { // set $url to the redirect and repeat the loop

			if ($redirect && substr($redirect, 0, strlen('http')) != 'http') { // If it's a relative (not absolute) url ...
				logToFile($logfile, "$redirect does NOT start with 'http'. Instead, it starts with " . substr($redirect, 0, strlen('http')));
				$domainLength = strpos ( $url, "/", 8 ); 
				$proto_dom = substr($url, 0, $domainLength); // up to the 1st slash after position 8 (i.e., skip "https://") 
				logToFile($logfile, "proto_dom: " . $proto_dom);
				$url = $proto_dom . $redirect; // ... prepend the protocol & domain.
			} else {
				logToFile($logfile, "$redirect DOES start with 'http'.");
				$url = $redirect;
			}
			
		} else { // we found the endpoint so break out of loop
			logToFile($logfile, "\$redirect is null so ending loop with endpoint: " . substr( $url , 0 , 90 ));
			logToFile($logfile, "findEndpoint returning");
			return ($url);
		}
	}
} // findEndpoint ($url) Return endpoint (i.e., URL has multiple redirects to a final destination)

function CheckRedirect($url){
// Return redirect target
// http://forums.hscripts.com/viewtopic.php?f=11&t=5210
	$logfile = $GLOBALS['logfile'];
	
	global $debug;
	$debug .= "<p>CheckRedirect() about to check " . substr( $url , 0 , 90 ) . "<p>";
	$user_agent = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:10.0.7) Gecko/20100101 Firefox/10.0.7';
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow any "Location: " header that the server sends as part of the HTTP header (note this is recursive, PHP will follow as many "Location: " headers that it is sent, unless CURLOPT_MAXREDIRS is set).
	curl_setopt($ch, CURLOPT_HEADER, true); // include the header in the output.
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return the transfer as a string of the return value of curl_exec() instead of outputting it out directly
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent); // The contents of the User-Agent:  header to be used in a HTTP request.
//	curl_setopt($ch, CURLOPT_AUTOREFERER, true); // Automatically set the Referer: field in requests where it follows a Location: redirect.

	
	curl_setopt($ch, CURLOPT_URL, $url);
	$out = curl_exec($ch);
//	$debug .= "<pre>" . print_r($out, TRUE) . "</pre>";
	
	$out = str_replace("\r", "", $out);
	
	$headers_end = strpos($out, "\n\n");
	if( $headers_end !== false ) { 
		$out = substr($out, 0, $headers_end);
	}   
	$headers = explode("\n", $out);
	
	foreach($headers as $header) {
//		if( substr($header, 0, 10) == "Location: " ) { 
		if( strtolower(substr($header, 0, 10))  == "location: " ) { 
			$target = substr($header, 10);	
			//echo "[$url] redirects to [$target]<br>";
			return $target;
		}   
	}   	
	return null;
} // CheckRedirect($url) Return redirect target

function SkimLinkShortener () {
	$logfile = $GLOBALS['logfile'];

	$user = $_GET['user'];
	$longUrl = $_GET['url'];
	$url = "http://buyth.at/-make?user=" . $user . "&url=" . $longUrl;
	
	$ch = curl_init(); // create curl resource 
	curl_setopt($ch, CURLOPT_URL, $url); // set url 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return the transfer as a string 
	$output = curl_exec($ch); // $output contains the output string 
	curl_close($ch); // close curl resource to free up system resources 
    
    return($output);	
}


function logToFile($filename, $msg) { 
// http://www.devshed.com/c/a/PHP/Logging-With-PHP/1/#ccJi5GVsszHo1aYy.99 
	return;
	$debug = $GLOBALS['debug'];
	if ($debug) {
		$fd = fopen($filename, "a"); // open file  
		$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg; // append date/time to message 
		fwrite($fd, $str . "\n"); // write string
		fclose($fd); // close file		
	}
}

?>