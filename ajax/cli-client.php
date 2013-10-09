<?php
require_once "client-download-and-process-class.php";
echo "..." . PHP_EOL;
/**
* Usage: "php client-class.php <int setNumber>"
*/
/*
$setNumber = (isset($argv[1]) && $argc==2 ) ? $argv[1] : die("Usage: 'php client-class.php <int setNumber>'" . PHP_EOL);
$client = new ClientDownloadAndProcessSet($setNumber);
$client->selectUrls();
echo $client->processUrls();
echo "All done!" . PHP_EOL;
*/


$urlsJson = (isset($argv[2]) && $argc==3 ) ? $argv[2] : die("Usage: 'php client-class.php <seconds delay between downloads> <JSON set of urls>'" . PHP_EOL . 'JSON should look like ["http://www.retailmenot.com/view/art.com","http://www.retailmenot.com/view/athleta.com"]' . PHP_EOL . "Include the brackets and double-quotes." . PHP_EOL . PHP_EOL);

$sleep = $argv[1];

$urlsJson = '["http://www.retailmenot.com/view/art.com","http://www.retailmenot.com/view/athleta.com"]';

$urls = json_decode($urlsJson);

$client = new ClientDownloadAndProcessUrls($urls, $sleep);
$urlClientProcessUrlsOutpout = $client->processUrls();
echo "\$urlClientProcessUrlsOutpout: " . print_r($urlClientProcessUrlsOutpout,TRUE) . PHP_EOL;

echo "All done!" . PHP_EOL;


?>