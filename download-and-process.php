<?php

	define('LOCAL', 'localhost');
	define('DEVELOPMENT', 'dev.tipjardaily.com');
	define('PRODUCTION', 'tipjardaily.com');
	define('DIR', dirname(__FILE__)); // e.g., /home/tipjardaily/tipjardaily.com/tools-dev
	
	$debug = "";
?>

<!DOCTYPE html>
<html lang="en" ng-app>
<head>
	<meta charset="utf-8">
	<title>Download and Process</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="scrapings">
	<meta name="author" content="Me">

	<!-- Le styles -->
	<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="../../assets/js/html5shiv.js"></script>
      <script src="../../assets/js/respond.min.js"></script>
    <![endif]-->
    <style>
    	body { padding-top: 70px; };
	</style>
   
</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="collapse navbar-collapse navbar-ex1-collapse">
			<ul class="nav navbar-nav">
				<li class="active"><a href="#">Download & Process</a></li>
				<li><a href="index.php">Data</a></li>
			</ul>
	</nav>
	<div class='row'>
		<div id='alerts' class='col-md-11 col-md-offset-1'>
		</div>
	</div>
	<div class='row'>
		<div id='controls' class='col-md-11 col-md-offset-1'>
		</div>
		<div id='finder' class='col-md-11 col-md-offset-1'>
		</div>
	</div>
    <div class='row'>
    	<div id='output' class='col-md-12'>
    	</div>
    </div>
    <div class='row'>
    	<div id='data' class='col-md-12'>
    	</div>
    </div>
    <div class='row'>
    	<div id='debug' class='col-md-12'>
    		<?php echo $debug; ?>
    	</div>
    </div>
	
    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
    <script src="http://yui.yahooapis.com/3.12.0/build/yui/yui-min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.js"></script>
    <script src="assets/js/dlpjs.php?version=<?php echo(rand())?>"></script>
    <script>
		console.log("About to call init");
		$( init );
    </script>


</body>
</html>