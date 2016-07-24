<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$language_id = $data->language_id;
/*$language_id = 1;*/
/*$language_id = 11;*/
require_once '../../includes/Database.php';
require_once '../../includes/Language.php';
try {
	if (is_numeric($language_id)) {
	$output_array = array('translations_to' => array(), 'translations_from' => array());
	$mysqli = Database::connection();

	if ($language_id == 11) {
		//All selection
		$sql = "SELECT COUNT(*) FROM translations";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$total = mysqli_fetch_row($result)[0];
		$output_array['translations_to'] = $total;
		$output_array['translations_from'] = $total;
		echo json_encode($output_array, JSON_PRETTY_PRINT);
		http_response_code(200);
	}
	else {
		$sql = "SELECT to_language_id as `language_id`, COUNT(translation_id) as `translations_to`
		FROM translations
		WHERE to_language_id = '$language_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$row_to = mysqli_fetch_array($result, MYSQLI_ASSOC);

		//Selection of count based on how many translations are from this language
		$sql = "SELECT t2.language, COUNT(t1.translation_id) as `translations_from`
		FROM translations as t1
		INNER JOIN functions as t2 
		ON t2.function_id = t1.from_function_id
		WHERE t2.language = '$language_id'
		";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$row_from = mysqli_fetch_array($result);

		$output_array = array(
			'translations_to' => $row_to['translations_to'],
			'translations_from' => $row_from['translations_from']
		);
		echo json_encode($output_array, JSON_PRETTY_PRINT);
		http_response_code(200);

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

