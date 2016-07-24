<?php
error_reporting(0);	
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
if (isset($_GET['src'])) {
	if ($_GET['src'] == 'simple') {
		$src = 'simple';
	}
	else {
		$src = 'complex';
	}
}
else {
	$src = 'complex';
}
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/angucomplete-alt.css">
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="view = '<?php echo $src;?>'">
		<div class="row">
			<?php require_once 'navbar.php'; //Render the navbar in the DOM?>
			<div class="col-md-10 col-md-offset-1">
				<div class="row">
					<div class="col-md-12">
						<p style="display:inline;float:right;">
							<a href ng-click="view = 'single'" style="margin-left:5px;">Function View</a> | 
							<a href ng-click="view = 'complex'" style="margin-right:5px;">Question View</a>
						</p>
					</div>
				</div>
				<form ng-show="view == 'single'">
					<div class="row">
						<div class="col-md-12">
							<p>
								Translations pages for single functions are automatically created if the translation is requested and it is found to not yet exist in the database. You can request one at the default search bar at the home page or here. To request one, simple type your function or method and a typeahead should search through all indexed function in the database. If the function is not found, it means that the it has not been indexed in our database and an option should appear allowing you to.
							</p>
						</div>
						<div class="col-md-12">
							<p>
								<small style="text-align:right;display:block;"><strong>Supported languages: PHP, Javascript, C, Python, Ruby, Swift, Java, C++, C#</strong></small>
							</p>
						</div>
						<div class="col-md-8">
<!-- 							<input type="text" ng-model="sourceFunction" class="form-control" placeholder="Search a function/method/concept to be paraphrased in another language"> -->
								<angucomplete-alt id="members"
					              placeholder="Search a function"
					              pause="400"
					              selected-object="sourceFunction"
					              remote-url="http://paraphrase.io/postRequests/addFunction/searchFunction.php?function="
					              title-field="title"
					              description-field="description"
					              input-class="form-control form-control-small"/>
							<p class="help-text" style="text-align:left;">
								<small>Search the function database on the site</small>
							</p>
						</div>
						<div class="form-group">
							<label for="destinationLanguage" class="col-md-1 control-label">Destination Language</label>
							<div class="col-md-2">
								<select ng-model="selectedDestinationLanguageID" ng-options="language.language_id as language.language_name for language in languages" required class="form-control">
								</select>
							</div>
						</div>
				        <div class="col-md-1">
							<input type="submit" value="Submit" ng-click="postParaphrase()" class="btn btn-info">
				        </div>
				    </div>
				</form>
				<form ng-show="view == 'complex'" class="form-horizontal">
					<div class="form-group">
						<p class="col-md-8 col-md-offset-2">
							Questions on paraphrase.io should mostly be covering or include concepts, abstract topics, and other related content that can otherwise not be embodied simply within a function or require further attention and specifications and should stray away from "easy" translation requests that simply contain a snippet of code with a desire to be translated into a different language without specifiying details, summaries, and preferrably programming language technicalities. Questions should strive to further the knowledge base of the site and producing relevant information in order to engage the community and others who may reach similar conflicts or assistance.
						</p>
					</div>
					<div class="form-group">
						<label for="phrase" class="col-md-2 control-label">Title</label>
						<div class="col-md-8">
							<input type="text" ng-model="question.title" class="form-control" id="phrase" required minlength="5">
							<p class="help-text">
								Titles should be succint and identify content easily in order to aid search
							</p>	
						</div>
					</div>
					<div class="form-group">
						<label for="sourceLanguage" class="col-md-2 control-label">From</label>
						<div class="col-md-3">
							<select ng-model="selectedSourceLanguageID" ng-options="language.language_id as language.language_name for language in languages" id="sourceLanguage" class="form-control">
							<option value='' disabled selected style='display:none;'>Choose Source Language</option>
							</select>
						</div>
						<label for="destinationLanguage" class="col-md-2 control-label">To</label>
						<div class="col-md-3">
							<select ng-model="selectedDestinationLanguageID" ng-options="language.language_id as language.language_name for language in languages" class="form-control">
							<option value='' disabled selected style='display:none;'>Choose Destination Language</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-8 col-md-offset-2">
							<textarea rows="10" class="form-control" ng-model="question.body"></textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-1 col-md-offset-2">
							<input type="submit" ng-click="postQuestion()" class="btn btn-primary" value="Submit">	
						</div>
					</div>
				</form>
			</div>
		</div>
	</body>
</html>
<script src="/bower_components/angular/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="\bower_components\angucomplete-alt\dist\angucomplete-alt.min.js"></script>
<script src="/angular/postquestion/post_question.js"></script>
