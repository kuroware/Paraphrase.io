<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
require_once 'navbar.php';
/*require_once 'includes/database.php';*/

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$id = $_GET['id'];
}
//Add parameter for tab active
else {
	die('Could not find language');
}
$user_id = User::get_current_user_id();
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="init('<?php echo $id;?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
<!-- 				<ul class="breadcrumb" style="padding-left:0px;">
					<li><a href="index.php">Home</a></li>
					<li><a href="#">Languages</a></li>
					<li class="active">{{ ::language.language_name}}</li>
				</ul> -->
			<!-- 	<div class="page-header" style="margin-top:0px;"> -->
					<!-- <img ng-src="{{language.icon}}" height="80px;" style="display:inline;"> --><h2 style="display:inline;">{{ ::language.language_name}}</h2>
					<hr style="margin-top:5px;margin-bottom:0px;">
					Latest Stable Release: {{ ::language.version}}
			<!-- 	</div> -->
				<tabset>
					<tab heading="Summary">
						<p ng-bind-html="language.summary"></p>
					</tab>
					<tab heading="Indexed Functions">
						<div class="row" style="margin-top:5px;">
							<div style="float:right;" class="col-md-6">
								<input type="text" class="form-control" placeholder="Search functions in {{:: language.language_name}}" ng-model="data.search" minlength="2">
							</div>
						</div>
						<table class="table table-striped table-hover">
				<!-- 			<a href>Add Function</a> -->
							<thead>
								<tr>
									<th>Index</th>
									<th>Function Name</th>
									<th width="50%">Description</th>
									<th>Type</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="function in data.filteredFunctions">
									<td>{{$index + ((data.currentPage - 1) * data.numPerPage)}}</td>
									<td><a ng-href="function.php?id={{function.function_id}}">{{ ::function.function_name}}</a></td>
									<td>{{ ::function.description | limitTo:100}}...</td>
									<td>{{ ::function.super_description}}</td>
								</tr>
							</tbody>
						</table>
						<pagination 
						  ng-model="data.currentPage"
						  total-items="data.functions.length"
						  max-size="data.maxSize"
						  items-per-page="data.numPerPage"  
						  boundary-links="true">
						</pagination>
					</tab>
					<tab heading="Features">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th width="20%">Feature</th>
									<th>Status</th>
									<th>Summary</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="feature in language.features">
									<td>{{ ::feature.feature_name}}</td>
									<td>{{ ::feature.status}}</td>
									<td>{{ ::feature.summary}}</td>
								</tr>
							</tbody>
						</table>
					</tab>
					<tab heading="Related Content">
						Remember to code this...
					</tab>
					<tab heading="Top Users"></tab>
				</tabset>
			</div>
		</div>
	</body>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/angularJS/angular-sanitize.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="angular/language/language.js"></script>