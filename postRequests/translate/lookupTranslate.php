<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Comment')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Comment.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}
}
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
/*require_once('../../includes/database.php');*/
$lan1 = $data->lan1;
$lan2 = $data->lan2;
$from_function_id = $data->from_function_id;
$category_id = $data->category_id;
/*	$lan1 = 1;
	$lan2 = 2;	
	$from_function_id = 75;
	$category_id = 0;*/
try {
	if (is_numeric($from_function_id) && is_numeric($lan2) && $lan1 != $lan2) {
/*		require_once('../../includes/language.php');
		require_once('../../includes/LanguageFunction.php');
		require_once('../../includes/Translate.php');*/

		$mysqli = Database::connection();
		$mysqli->set_charset('utf8'); //Forced

		$sql = "SELECT language, category_id FROM functions WHERE function_id = '$from_function_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		if ($result->num_rows == 1) {
			$row = mysqli_fetch_row($result);
			$lan1 = $row[0];
			$category_id = $row[1];
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
		}

		$from_language = new Language(array(
			'language_id' => $lan1)
		);
		$to_language = new Language(array(
			'language_id' => $lan2)
		);
		$from_function = new LanguageFunction(array(
			'function_id' => $from_function_id,
			'function_language' => $from_language, 
			'category_id' => $category_id)
		);
		$incoming_translation = new IncomingTranslation(array(
			'from_function' => $from_function,
			'to_language' => $to_language)
		);

		if ($to_language->language_id != $from_language->language_id) {

			//Now attempt the translations
			$result = TranslateFactory::translate($incoming_translation, false, true);
			echo json_encode($result, JSON_PRETTY_PRINT);
		}

		//Finally log that this translation took place		
		Database::log_translation();
		
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
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}