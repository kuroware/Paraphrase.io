<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
require_once 'navbar.php';
try {
	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		$post_id = $_GET['id'];
		$mysqli = Database::connection();
		$sql = "SELECT t1.post_id, t1.post_text, t1.post_title, t1.date_posted, t2.username as `author_username`, t1.author_id
		FROM posts as t1
		LEFT JOIN users as t2
		ON t2.user_id = t1.author_id
		WHERE t1.post_id = '$post_id'";
		$result = $mysqli->query($sql)
		or die($mysqli->error);

		if ($result->num_rows == 1) {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
			$post = new Post($row);
			$post->author->username = $row['author_username'];
		}
		else {
			throw new OutOfRangeException;
		}
	}
	else {
		throw new UnexpectedValueException;
	}
}
catch (Exception $e) {
	echo 'nooo';
	//header('Location: /404.php');
}
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete/angucomplete.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">
		<link rel="stylesheet" type="text/css" href="/css/view_post.css">
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">
	</head>
	<body ng-controller="main" class="container-fluid">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<h2><?php echo $post->post_title;?></h2>
				<p>
					<?php echo nl2br($post->post_text);?>
				</p>
			</div>
		</div>
	</body>
</html>
<script src="/bower_components/angular/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<!-- <script src='/bower_components/textAngular/dist/textAngular-rangy.min.js'></script>
<script src='/bower_components/textAngular/dist/textAngular-sanitize.min.js'></script>
<script src="/bower_components/textAngular/dist/textAngular.min.js"></script> -->
<script src="/angular/post/post.js"></script>