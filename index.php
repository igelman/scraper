<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
//echo __FILE__; // returns absolute path to this file
//echo ""<br>"" . dirname(__FILE__); // returns parent directory of this file
//echo ""<br>"" . time();
//phpinfo();

include("ajax/file-downloader-class.php");
include_once("config/scraper.config");

$appRootPath = $GLOBALS['appRootPath'];
$fileStorePath = $GLOBALS['fileStorePath'];


$urls = array(
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gap-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gamefly-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gamestop-20130904-1140.html",
			"http://localhost/development/scraper/tests/sample-files/gap-20130904-1140.html",
		);

/*
echo "urls: " . "<br>";
print_r($urls) . "<br>";
echo "appRootPath: " . $appRootPath . "<br>";
echo "fileStorePath: " . $fileStorePath . "<br>";
*/


$fd = new FileDownloader($urls);
$fd->setAppRootPath($appRootPath);
$fd->setFileStorePath($fileStorePath);
$fd->createCurlMultiHandler();
$fd->storeFiles();


?>