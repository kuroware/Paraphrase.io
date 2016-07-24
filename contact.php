<?php
	function __autoload($class_name) {
		/*
		Last chance for PHP script to call a class name
		 */
		if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
		}
		else {
			require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
		}	
	}
	$user_id = User::get_current_user_id();

	if ($user_id != 'None') {
		$user = new User(array(
			'user_id' =>  $user_id)
		);
		$user->get_fields();
	}
	else {
		$user = new User(array(
			'user_id' => 3,
			'username' => 'Anonymous'
			)
		);
	}
	require_once 'navbar.php'; //Call the navbar
?>
<html ng-app="app">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="css/basic.css">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog.css">
	<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog-theme-default.css">
	<link rel="stylesheet" type="text/css" href="css/angucomplete/angucomplete.css">
</head>
<body class="container-fluid" ng-controller="controller">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<form class="form-horizontal">
				<legend>
					<h3>Contact Us</h3>
				</legend>
				<fieldset>
					<div class="form-group">
						<label for="username" class="col-md-1 control-label"><strong>Username</strong></label>
						<div class="col-md-10">
							<p class="form-control-static">
								<?php echo $user->username;?>
							</p>
						</div>
					</div>
					<div class="form-group">
						<label for="comment" class="col-md-1 control-label"><strong>Comment</strong></label>
						<div class="col-md-10">
							<textarea ng-model="comment" class="form-control" rows="6">
							</textarea>
							<p class="help-block">
								Anything! Ideas, criticism, suggestions, requests, bugs you've noticed (please don't <small>pwn</small> us) or just because you're cool. We read <strong>all</strong> of them
							</p>
						</div>
					</div>
					<label class="radio-inline">
						<input type="radio" class="control-label" value="Bug" ng-model="reason">
						Bug
					</label>
					<label class="radio-inline">
						<input type="radio" class="control-label" value="Account Related" ng-model="reason">
						Account Related
					</label>
					<label class="radio-inline">
						<input type="radio" class="control-label" value="Feedback" ng-model="reason">
						Feedback
					</label>
					<label class="radio-inline">
						<input type="radio" class="control-label" value="Other" ng-model="reason">
						Other
					</label>
					<br/>
					<label class="checkbox-inline">
						<input type="checkbox" class="control-label" ng-true-value="true" ng-false-value="false" ng-model="developer">
						Developer
					</label>
					<label class="checkbox-inline">
						<input type="checkbox" class="control-label" ng-true-value="true" ng-false-value="false" ng-model="moderator">
						Moderator
					</label>
					<p class="help-block">
						<small>
							If you wish to notify that you'd be interesting in developing with us, please check "Developer". If you'd be interested in becoming a moderator once the site reputation stablizes, we will check your account first
						</small>
					</p>
					<input type="submit" value="Submit" class="btn btn-default" ng-click="submit()">
				</fieldset>
			</form>
		</div>
	</div>
</body>
</html>
<script src="bower_components/angular/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="angular/contact/contact.js"></script>