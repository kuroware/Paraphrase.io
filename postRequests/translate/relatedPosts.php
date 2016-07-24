<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$to_lan_id = $data->to_lan_id;
$from_function_id = $data->from_function_id;
try {
	if (is_numeric($to_lan_id) && is_numeric($from_function_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT t1.post_id, t3.post_text, t3.post_title, t3.author_id, t4.username as `author_username`
		FROM tagged_posts_to_translations as t1
		INNER JOIN (
			SELECT translation_id
			FROM translations
			WHERE to_language_id = '$to_lan_id' AND from_function_id = '$from_function_id'
		) as t2
		ON t2.translation_id = t1.translation_id
		INNER JOIN posts as t3
		ON t3.post_id = t1.post_id
		LEFT JOIN users as t4 
		ON t4.user_id = t3.author_id
		";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$posts = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$post = new Post($row);
			$post->author->username = $row['author_username'];
			$posts[] = $post;
		}
		echo json_encode($posts, JSON_PRETTY_PRINT);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}


?>