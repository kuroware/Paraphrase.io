<?php
require_once 'navbar.php';
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
?>
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
				<div class="col-md-8 col-md-offset-1">
					<input type="text" ng-model="searchPhrase" class="form-control" placeholder="Search a function/method/concept to be paraphrased in another language">
					<p class="help-text" style="text-align:left;">
						<small>Try searching a function like: "strtoupper()", a method "Quicksort" or a concept "Sending a $http request in AngularJS"</small>
					</p>
				</div>
		        <div class="col-md-2">
					<select ng-model="selectedLanguageID" class="form-control" required>
						<option ng-repeat="language in languages" ng-selected="language.language_id == selectedLanguageID" ng-value="language.language_id" ng-show="language.language_id != 11">{{language.language_name}}</option>
					</select>
		        </div>
				<div class="col-md-1 col-md-push-10">
				</div>
				<div class="row">
					<div class="col-md-8 col-md-offset-2" style="text-align:center;">
						<button class="btn btn-primary" ng-click="search()">Search</button>
						<button class="btn btn-info">Paraphrase</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
<script src="/bower_components/angular/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="/angular/index/index.js"></script>