<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Edit')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Edit.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$translation_id = $data->translation_id;
/*$translation_id = 5;*/
try {
	if (is_numeric($translation_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT DATE_FORMAT(date, '%b %e, %Y') as date, views FROM `translation_request_views_integral` WHERE translation_id = '$translation_id' ORDER BY date";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$labels = array();
		$dataset = array(array());
		if ($result->num_rows > 0) {
			while ($row = mysqli_fetch_array($result)) {
				$labels[] = $row['date'];
				$dataset[0][] = $row['views'];
			}
		}
		else {
			//Create dummy data
			$curdate = date('M j, Y');
			$labels = array($curdate);
			$dataset = array(array(0));
		}

		//Get the language name quickly
		$sql = "SELECT t2.language_name
		FROM translations as t1 
		INNER JOIN languages as t2 
		ON t2.language_id = t1.to_language_id
		WHERE t1.translation_id = '$translation_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$language_name = mysqli_fetch_row($result)[0];

		echo json_encode(array(
			'labels' => $labels,
			'dataset' => $dataset,
			'series' => array($language_name)
			), JSON_PRETTY_PRINT
		);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);	
}