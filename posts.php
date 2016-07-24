<?php
//Autoloading function
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
require_once 'navbar.php';
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete/angucomplete.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">
		<link rel="stylesheet" type="text/css" href="/css/post.css">
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">
	</head>
	<body ng-controller="main" class="container-fluid">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div ng-repeat="post in posts" class="post">
					<div class="postTitle">
						<h2><a ng-href="/post.php?id={{post.post_id}}">{{post.post_title}}</a></h2>
						<h4><a ng-href="profile.php?id={{post.author.user_id}}">{{post.author.username}}</a></h4>
					</div>
					<p>
						{{post.post_text | limitTo:750}}....<a ng-href="post.php?id={{post.post_id}}">Read More</a>
					</p>
				</div>
			</div>
		</div>
	</body>
</html>
<script src="/bower_components/angular/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="/angular/posts/posts.js"></script>