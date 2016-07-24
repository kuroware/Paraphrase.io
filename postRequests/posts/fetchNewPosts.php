<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
$mysqli = Database::connection();
$mysqli->set_charset('utf8');
$sql = "SELECT post_id, post_text, date_posted, author_id, post_title FROM posts ORDER BY date_posted DESC";
$result = $mysqli->query($sql)
or die ($mysqli->error);
$posts = array();
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$post = new Post($row);
	$posts[] = $post;
}
echo json_encode($posts, JSON_PRETTY_PRINT);
?>