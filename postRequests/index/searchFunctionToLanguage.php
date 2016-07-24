<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
require_once __DIR__ . '/../../includes/database.php';
$some_function = $data->some_function;
/*$some_function = 'str';*/
try {
	if ($some_function) {
		require_once __DIR__ . '/../../includes/language.php';
		$some_function = trim(Database::sanitize($some_function), '.');
		$mysqli = Database::connection();
		$sql = "SELECT t2.language_id, t2.language_name
		FROM functions as t1
		LEFT JOIN languages as t2
		ON t2.language_id = t1.language
		WHERE t1.function_name LIKE '$some_function%'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$languages = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$language = new Language($row);
			array_push($languages, $language);
		}
		echo json_encode($languages, JSON_PRETTY_PRINT);
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