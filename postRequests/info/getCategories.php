<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$type = $data->type;
require_once '../../includes/database.php';
try {
	if (is_numeric($type)) {
		$mysqli = Database::connection();
		$sql = "SELECT category_id, description FROM category WHERE type = '$type'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$supers = array();
		$supers[] = null;
		while ($row = mysqli_fetch_array($result)) {
			array_push($supers, $row);
		}
		echo json_encode($supers, JSON_PRETTY_PRINT);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
		http_response_code(200);
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}