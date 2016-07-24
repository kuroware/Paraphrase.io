<?php require_once 'navbar.php';?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete/angucomplete.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">
		<link rel="stylesheet" type="text/css" href="/css/index.css">
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">
	</head>
	<body ng-controller="main" class="container-fluid">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="jumbotron">
					<h3>404 - Could not find page</h3>
					You are likely looking for a page that does not exist (or at least does not yet exist) on this website (or the alternative is that we messed up on our code).
					<br/>
					<a href="/index.php" class="btn btn-info">Go back home</a>
				</div>
			</div>
		</div>
	</body>
</html>
<script src="/bower_components/angular/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="/angular/404/404.js"></script>