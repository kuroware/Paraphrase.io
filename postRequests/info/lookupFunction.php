<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$function_id = $data->function_id;
//$function_id = 1;
require_once '../../includes/Language.php';
require_once '../../includes/LanguageFunction.php';
require_once '../../includes/Database.php';
$mysqli = Database::connection(); //Connection variable
try {
	if (is_numeric($function_id)) {
		$sql = "SELECT t1.function_id, t1.category_id, t1.language as `language_id`, t1.function_name, t1.description, t1.link, t1.syntax, t1.date, t2.language_name
		FROM functions as t1
		LEFT JOIN languages as t2
		ON t2.language_id = t1.language
		WHERE t1.function_id = '$function_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if ($result->num_rows == 1) {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
			$row['function_language'] = new Language(array(
				'language_id' => $row['language_id'],
				'language_name' => $row['language_name'])
			);
			$function = new LanguageFunction($row);
			echo json_encode($function, JSON_PRETTY_PRINT);
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
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}