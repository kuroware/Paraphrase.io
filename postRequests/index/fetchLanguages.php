<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$dev = $data->dev;
$dev = true;
try {
	if ($dev) {
		require_once __DIR__ . '/../../includes/database.php';
		require_once __DIR__ . '/../../includes/Language.php';
		$mysqli = Database::connection();
		$sql = "SELECT language_id, language_name
		FROM languages
		";
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
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}