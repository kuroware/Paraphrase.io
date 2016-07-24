<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$user_id = $data->user_id;
/*$user_id = 2;*/
//require_once __DIR__ . '/../../includes/User.php';
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

try {
	if (is_numeric($user_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT points, DATE_FORMAT(date, '%b %e, %Y') as date_formatted FROM integral_users WHERE user_id = '$user_id' ORDER BY date ASC";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		//require_once '../../includes/FusionChart.php';

		$dates = array();
		$dataset = array();

		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$dataset[] = $row['points'];
			$dates[] = $row['date_formatted'];
		}
		$charts = array($dates, $dataset);
		echo json_encode($charts, JSON_PRETTY_PRINT);
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