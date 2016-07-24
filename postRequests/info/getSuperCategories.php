<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$dev = $data->dev;
$dev = true;
require_once '../../includes/database.php';
try {
	if ($dev) {
		$mysqli = Database::connection();
		$sql = "SELECT super_id, description FROM super_category
		ORDER BY description ASC";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$supers = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$supers[] = $row;
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