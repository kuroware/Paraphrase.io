<?php
require_once 'navbar.php'; //Call the navbar
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete-alt.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">
		<link rel="stylesheet" type="text/css" href="/css/index.css">
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
	</head>
	<body ng-controller="controller" class="container-fluid">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
			<h3>Swift</h3>
				Full Swift functionality is currently pending alongside Java, C++, and C#. You can check the <a href="updatelog.php">update log</a> for more information, progress, and possibly to join/contribute at <a href="contact.php">this link</a>.
			<hr>
			<h3>Other Possible Languages</h3>
				<h5>C++, C, and C#</h5>
				C languages as currently pending due to the extensive foriegn nature from these languages from other languages in terms of coding (PHP and Javascript are largely using built-in methods with no complex modules, a few exceptions). They will be implemented based on community feedback and some progress has been made on. 
				<h5>AngularJS</h5>
				AngularJS is another possible language that could be added, as it has an extensive library and should be considered a distinct framework from VanillaJS. 
				<br/>
				<br/>
				<h5>Ruby on Rails</h5>
				Another possible language may be Ruby on Rails, but we currently do not have enough information (or suggestion) or knowledge to begin categorizing and implementing it. 
			</div>
		</div>
	</body>
</html>
<script src="bower_components/angular/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="bower_components/marked/lib/marked.js"></script>
<script src="bower_components/angular-marked/angular-marked.js"></script>
<script src="angular/app.js"></script>
