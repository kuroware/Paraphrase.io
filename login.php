<?php
session_start();
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
/*require_once 'includes/database.php';
require_once 'includes/user.php';*/
/*	$return_val = array();
	$return_val['session'] = $_SESSION['user_id'];
	$return_val['token'] = $_COOKIE['token'];
	$return_val['cookie'] = $_COOKIE['user_id'];
	echo json_encode($return_val, JSON_PRETTY_PRINT);
$user_id = User::get_current_user_id();
echo $user_id;*/
/*$return_val['session'] = $_SESSION['user_id'];
$return_val['token'] = $_COOKIE['token'];
$return_val['cookie'] = $_COOKIE['user_id'];
echo json_encode($return_val, JSON_PRETTY_PRINT);*/
$user_id = User::get_current_user_id();
if (isset($_GET['tab'])) {
	if ($_GET['tab'] == 'sign') {
		$tab = 'sign';
	}
	else {
		$tab = 'log';
	}
}
else {
	$tab = 'log';
}
?>
<?php
require_once 'navbar.php';
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/index.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="init('<?php echo $user_id;?>', '<?php echo $tab;?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<tabset>
					<tab active="tab.log">
						<tab-heading>
							Login
						</tab-heading>
						<form class="form-horizontal">	
							<fieldset>
								<legend>Login</legend>
								<p style="color:red" ng-show="errorMessage">
									{{errorMessage}}
								</p>
								<div class="form-group">
									<label for="username" class="col-md-1 control-label">Username</label>
									<div class="col-md-5">
										<input type="text" class="form-control" id="username" ng-model="login.username" required>
									</div>	
								</div>
								<div class="form-group">
									<label for="password" class="col-sm-1 control-label">Password</label>
									<div class="col-md-5">
										<input type="password" class="form-control" id="password" ng-model="login.password" required>
										<p class="help-block text-left">
											<small>
												Forgot your password? Contact us about it <a href="contact.php">here</a>.
											</small>
										</p>
									</div>
								</div>
								<div class="col-md-1 form-group">
									<input type="submit" ng-click="login()" class="btn btn-default" value="Login">
								</div>
							</fieldset>
						</form>
					</tab>
					<tab active="tab.sign">
						<tab-heading>
						Sign Up
						</tab-heading>
						<form class="form-horizontal" ng-controller="register"> 
							<fieldset>
								<legend>Sign Up</legend>
								<div ng-class="register.usernameClass">
									<label for="username" class="col-md-1 control-label">Username</label>
									<div class="col-md-5">
										<input type="text" class="form-control" id="username" placeholder="Your desired username" ng-model="register.username" required minlength="5" ng-click="register.initialUsername = true">
										<p class="help-block" style="font-color:red">{{register.usernameErrorMessage}}</p>
									</div>
								</div>
								<div class="form-group">
									<label for="email" class="col-md-1 control-label">Email</label>
									<div class="col-md-5">
										<input type="email" class="form-control" id="email" ng-model="register.email" required>
									</div>
								</div>
								<div ng-class="register.passwordClass">
									<label for="password" class="col-md-1 control-label">Password</label>
									<div class="col-md-5">
										<input type="password" class="form-control" placeholder="Your desired password" id="password" ng-model="register.password" minlength="5" required ng-click="register.initialPassword = true">
										<p class="help-block" style="font-color:red">{{register.passwordErrorMessage}}</p>
									</div>
								</div>
								<div ng-class="register.confirmPasswordClass">
									<label for="password" class="col-md-1 control-label">Confirm Password</label>
									<div class="col-md-5">
										<input type="password" class="form-control" placeholder="Enter your password again" id="password" ng-model="register.confirmPassword" minlength="5" required ng-click="register.initialConfirm = true">
										<p class="help-block" style="font-color:red">{{register.confirmPasswordErrorMessage}}</p>
									</div>
								</div>
								<div class="form-group">
									<div class="col-md-1">
										<input type="submit" ng-click="register()" class="btn btn-default" value="Sign Up">
									</div>
								</div>
							</fieldset>
						</form>
					</tab>
				</tabset>
			</div>
		</div>
	</body>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="angular/login/login.js"></script>
