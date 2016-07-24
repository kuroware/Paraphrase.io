<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
/*require_once __DIR__ . '/../../includes/User.php';
require_once __DIR__ . '/../../includes/Database.php';*/
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

$request = file_get_contents('php://input');
$data = json_decode($request);
$filter = $data->filter;
/*$filter = 11;
*/
$mysqli = Database::connection();
try {
	$keys = range(1, 11);
	if (in_array($filter, $keys)) {
		if ($filter == 11) {
			$sql = "SELECT user_id, username, points, avatar
			FROM users
			WHERE user_id != 3
			ORDER BY points DESC
			LIMIT 10";
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
	$result = $mysqli->query($sql)
	or die ($mysqli->error);
	$users = array();
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$user = new User($row);
		$users[] = $user;
	}
	echo json_encode($users, JSON_PRETTY_PRINT);
	http_response_code(200);
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}

?>