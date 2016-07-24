<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$result_id = $data->result_id;
try {
	if (User::is_admin() && is_numeric($result_id)) {
		//User is an admin, delete
		$mysqli = Database::connection();
		$sql = "SELECT type FROM translation_results WHERE result_id = '$result_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		if ($result->num_rows == 1) {
			$type = mysqli_fetch_row($result)[0];
			switch ($type) {
				case 0:
					$sql = "DELETE FROM translation_multiple WHERE result_id = '$result_id' LIMIT 1";
					$result = $mysqli->query($sql)
					or die ($mysqli->error);
					break;
				case 1:
					$sql = "DELETE FROM translation_singled WHERE result_id = '$result_id' LIMIT 1";
					$result = $mysqli->query($sql)
					or die ($mysqli->error);
					break;
			}
			$sql = "DELETE FROM translation_results WHERE result_id = '$result_id' LIMIT 1";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			http_response_code(200);
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);
}
catch (OutOfRangeException $e) {
	http_response_code(400);
	Database::print_exception($e);
}