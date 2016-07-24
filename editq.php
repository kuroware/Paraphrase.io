<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
try {
	if ($_GET['id'] && is_numeric($_GET['id'])) {
		$mysqli = Database::connection();
		$edit_id = $_GET['id'];
		$user_id = User::get_current_user_id();
		$sql = "SELECT author_id FROM questions WHERE question_id = '$edit_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$author_id = mysqli_fetch_row($result)[0];
		if ($result->num_rows != 1) {
			throw new OutOfRangeException;
		}
		if ($author_id != $user_id) {
			throw new OutOfBoundsException;
		}
	}
	else {
		throw new UnexpectedValueException;
	}
}
catch (OutOfBoundsException $e) {
	die();
	//Not quite sure what to do yet if it's not the user's post

}
catch (OutOfRangeException $e) {
	die();
}
catch (UnexpectedValueException $e) {
	die();
}
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete/angucomplete.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">	
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">	
	</head>
	<body ng-controller="main" class="container-fluid" ng-init="init('<?php echo $edit_id;?>')">
		<div class="row">
			<?php require_once 'navbar.php';?>
			<div class="col-md-10 col-md-offset-1">
				<form class="form-horizontal">
					<div class="form-group">
						<div class="col-md-6 col-md-offset-2">
							<h5>Currently Editing...... <strong>{{question.title}}</strong></h5>
						</div>
					</div>
					<div class="form-group">
						<label for="phrase" class="col-md-2 control-label">Title</label>
						<div class="col-md-8">
							<input type="text" ng-model="question.title" class="form-control" id="phrase" required minlength="5" ng-value="question.title">
							<p class="help-text">
								Titles should be succint and identify content easily in order to aid search
							</p>	
						</div>
					</div>
					<div class="form-group">
						<label for="sourceLanguage" class="col-md-2 control-label">From</label>
						<div class="col-md-3">
							<select ng-model="question.src_language.language_id" ng-options="language.language_id as language.language_name for language in languages" id="sourceLanguage" class="form-control">
							<option value='' disabled selected style='display:none;'>Choose Source Language</option>
							</select>
						</div>
						<label for="destinationLanguage" class="col-md-2 control-label">To</label>
						<div class="col-md-3">
							<select ng-model="question.des_language.language_id" ng-options="language.language_id as language.language_name for language in languages" class="form-control">
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
							<input type="submit" ng-click="pushEdits()" class="btn btn-primary" value="Submit">	
						</div>
					</div>
				</form>
			</div>
		</div>
	</body>
</html>
<script src="/dependencies/angularJS/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="/angular/editQuestion/editQuestion.js"></script>