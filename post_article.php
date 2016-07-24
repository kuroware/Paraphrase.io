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
require_once 'navbar.php';
/*require_once __DIR__ . '/includes/User.php';
require_once __DIR__ . '/includes/Database.php';*/
$user_id = User::get_current_user_id();
$user = new User(array(
	'user_id' => $user_id)
);
if (!$user->user_id) {
	//The user is not logged in, die
	//header('Location: /login.php?message=You must be logged in to post an paraphrase article');
}
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">
		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/post_article.css">
		<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
		<link rel='stylesheet' type="text/css" href='/bower_components/textAngular/dist/textAngular.css'>
		<link rel="stylesheet" type="text/css" href="http://textangular.com/css/style.css">
	</head>
	<body ng-controller="main" class="container-fluid" ng-init="init('<?php echo htmlspecialchars(json_encode($user, JSON_PRETTY_PRINT))?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="row">
					<div class="col-md-12">
						<div text-angular ng-model="htmlVariable" rows="10" name="demo-editor" ta-text-editor-class="border-around" ta-html-editor-class="border-around">
					</div>
				</div>
			</div>
		</div>	
	</body>
</html>
<script src="/bower_components/angular/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src='/bower_components/textAngular/dist/textAngular-rangy.min.js'></script>
<script src='/bower_components/textAngular/dist/textAngular-sanitize.min.js'></script>
<script src="/bower_components/textAngular/dist/textAngular.min.js"></script>
<script src="/angular/articles/post_article.js"></script>