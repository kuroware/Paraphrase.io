<?php
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
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/angucomplete/angucomplete.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">
	</head>
	<body class="container-fluid" ng-controller="main">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<legend>
					<h3>Contribute</h3>
				</legend>
				The main way of contributing to the site asides from contacting us or promoting a welcoming and productive community is to add functions/methods and information that may be missing. You can add a function/method below and you can expect to see it within 24 hours (if it has not already been submitted). You can do a function check by simply searching in the search bar for the function. If there is to typeahead suggestion, then it likely does not exist, to further confirm, you can search without specifying a field and the function will be searched for in the entire site.
				<form class="form-horizontal">
					<legend>
						<h3>Add Function</h3>
					</legend>
					<p class="help-block">Langauge and function name required. Additional support for more languages coming soon. </p>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Language</label>
						<div class="col-sm-3">
							<select ng-model="selectedLanguage" ng-options="language.language_id as language.language_name for language in languages" class="form-control">
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Function Name</label>
						<div class="col-sm-6">
							<input type="text" ng-model="functionName" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Syntax</label>
						<div class="col-sm-6">
							<textarea ng-model="syntax" class="form-control" rows="3"></textarea>
							<p class="help-block">
								Syntax of a function, preferrably from its official documentation, to explain its expected format/arguments
								<br/>
								i.e. <code>array.splice(start, deleteCount[, item1[, item2[, ...]]])</code><em> from array.splice in JS</em>
							</p>
						</div>
					</div>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Summary</label>
						<div class="col-sm-6">
							<textarea rows="6" ng-model="summary" class="form-control"></textarea>
							<p class="help-block">
								Succint summary of a function/method to provide useful information about its general usage.
								<br/>Allowed tags: 
								<code>strong, code, em, i, u</code>
							</p>
						</div>
					</div>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Link</label>
						<div class="col-sm-6">
							<input type="text" ng-model="link" class="form-control">
							<p class="help-block">
								Link to official documentation (preferred) or helpful document.</br/>
								i.e. <a href="https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/String/indexOf">https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/String/indexOf</a>
							</p>
						</div>
					</div>
					<h3>Category</h3>
					<p class="help-block">Categorizing helps the database improve translations and allows for immediate answers to be completed. Optional, but recommended</p>
<!-- 					<h4>Categorize by analogous function</h4>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Identical Function</label>
						<div class="col-sm-6" ng-hide="selectedCat || selectedSuper">
							<angucomplete id="members"
				              placeholder="Search functions/methods"
				              pause="400"
				              selectedobject="identicalfunction"
				              url="http://localhost/codetranslator/postrequests/addFunction/searchFunction.php?function="
				              titlefield="title"
				              descriptionfield = "summary"
				              inputclass="form-control form-control-small">
				              </angucomplete>
				        <p class="help-block">
				       		You can categorize a function by selecting another function that exists in the database that is identical (in a different langauge) to the one you are adding. For example strtoupper() in PHP and .toUppercase() in Javascript
				   		</p>
						</div>
						<div class="col-sm-6" ng-hide="!(selectedCat || selectedSuper)">
							<input type="text" class="form-control" readonly>
					        <p class="help-block">
					       		You can categorize a function by selecting another function that exists in the database that is identical (in a different langauge) to the one you are adding. For example strtoupper() in PHP and .toUppercase() in Javascript
					   		</p>
						</div>
					</div> -->
					<h4>Categorize by manual options</h4>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Super Category</label>
						<div class="col-sm-6">
							<select ng-model="selectedSuper" ng-options="category.super_id as category.description for category in superCategories" class="form-control" ng-readonly="identicalfunction">
							</select>
						<p class="help-block">
				       		The super category a function falls into, best chosen. For example length can be a string or array function (i.e. arr.length is array and string.length is string)
				   		</p>
						</div>
					</div>
					<!--
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">Category</label>
						<div class="col-sm-6">
							<select ng-model="selectedCat" ng-options="category.category_id as category.description for category in categories" class="form-control" ng-readonly="identicalfunction || !selectedSuper">
							</select>
						<p class="help-block">
				       		The general category that best describes a function. If a better fit is possible without being too specific on frivolous details, you can add one below
				   		</p>
						</div>
					</div>
					<div class="form-group">
						<label for="function_name" class="col-sm-2 control-label">New Category</label>
						<div class="col-sm-6">
							<input type="text" class="form-control" ng-model="newCategory" ng-readonly="identicalfunction || !selectedSuper">
						<p class="help-block">
				       		A short general description of what the function/method does that is applicable to other languages' respective counterparts
				   		</p>
						</div>
					</div>
					-->	
					<input type="submit" ng-click="submitFunction()" class="btn btn-default">
				</form>
			</div>
		</div>
	</body>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/angucomplete-master/angucomplete.js"></script>
<script src="/dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="angular/add/add.js"></script>