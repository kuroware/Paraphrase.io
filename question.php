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
try {
	if (is_numeric($_GET['id'])) {
		$question_id = $_GET['id'];
		$user_id = User::get_current_user_id();
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	header('Location: /404.php');
}
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete/angucomplete.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">	
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">	
		<link rel="stylesheet" type="text/css" href="/css/question.css">
	</head>
	<body ng-controller="main" class="container-fluid" ng-init="init('<?php echo $question_id;?>', '<?php echo $user_id;?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="row">
					<div class="col-md-10 questionTitle">
							<h2>{{question.title}}</h2>
							<hr>
							<small>Posted {{question.date_posted_ago}} ago</small>
						{{question.body}}
					</div>
					<div class="col-md-10">
						<div class="row">
							<div class="col-md-12 statsBar">
								<small>
									<span ng-show="question.upvotes > 0">
										<span ng-show="question.upvoted">
											You <span ng-show="question.upvotes > 1">and {{question.upvotes - 1}} others</span>
											have upvoted this
										</span>
										<span ng-show="!question.upvotes">
											{{question.upvotes}} upvotes
										</span>
									</span>
									<span ng-show="question.downvotes > 0">
										<span ng-show="question.downvoted">
											You <span ng-show="question.downvotes > 1">and {{question.downvotes - 1}} others</span>
											have downvoted this
										</span>
										<span ng-show="!question.downvotes">
											{{question.downvotes}} downvotes
										</span>
									</span>
								</small>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 optionBox">
								<a href ng-click="upvote()">Upvote</a>
								<a href ng-click="downvote()">Downvote</a>
								<a ng-href="editq.php?id={{question.question_id}}" ng-show="user.user_id == question.author.user_id">Edit</a>
								<a href ng-click="report()">Flag as Inappropriate</a>
							</div>
							<div class="col-md-6">
								<div class="row">
									<div class="col-md-12">
										Tagged under {{question.source_language}}
									</div>
								</div>
							</div>
							<div class="col-md-1 authorBlock">
								<a ng-href="profile.php?id={{question.author.user_id}}">
									<img ng-src="{{question.author.avatar}}">
								</a>
							</div>
							<div class="col-md-3" class="authorBlock">
								<a ng-href="profile.php?id={{question.author.user_id}}">
									<h5>{{question.author.username}}</h5>
									{{question.author.points}}
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>	
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="/angular/question/question.js"></script>