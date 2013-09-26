<?php
$regex = '/(Expires|On|Ends)\s((([012])?\d\/[0123]?\d\/(20)?\d\d))/';

$contentArray = array(
	"Expires 9/30/2013",
	"On 9/30/2013",
	"Expires 10/30/2013",
	"Ends 10/30/2013",
	"Expires 9/3/2013",
	"Expires 10/3/2013",
	"Expires 9/30/13",
	"Expires 10/30/13",
	"Expires 9/3/13",
	"Expires 10/3/13",
);

foreach ($contentArray as $content) {
	preg_match($regex, $content, $matches);
	echo $content .PHP_EOL. print_r($matches, TRUE);
}
?>