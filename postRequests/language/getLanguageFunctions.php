<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$language_id = $data->language_id;
/*require_once '../../includes/database.php';*/
/*$language_id = 1;*/
try {
	if (is_numeric($language_id)) {
/*		require_once '../../includes/Language.php';
		require_once '../../includes/LanguageFunction.php';*/
		$mysqli = Database::connection();

		$sql = "SELECT language_name FROM languages WHERE language_id = '$language_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		if ($result->num_rows >= 1) {
			$row = mysqli_fetch_row($result);

			$cur_language = new Language(array(
				'language_id' => $language_id,
				'language_name' => $row[0])
			);

			$sql = "SELECT t1.function_id, t1.function_name, t1.category_id, t1.description, t2.category_description, t2.super_description
			FROM functions as t1
			LEFT JOIN (
				SELECT t1.category_id, t1.description as category_description, t2.super_id, t2.description as super_description
				FROM category as t1
				LEFT JOIN super_category as t2
				ON t2.super_id = t1.type
			) as t2
			ON t2.category_id = t1.category_id
			WHERE t1.language = '$language_id'
			ORDER BY t2.super_description ASC, t1.function_name ASC
			";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$functions = array();
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$row['function_language'] = $cur_language;
				$function = new LanguageFunction($row);
/*				if (!array_key_exists($row['super_description'], $functions)) {
					$functions[$row['super_description']] = array();
				}
				$functions[$row['super_description']][] = $function;*/
				$function->super_description = $row['super_description'];
				$functions[] = $function;
			}
			/*ksort($functions);*/
			echo json_encode($functions, JSON_PRETTY_PRINT);
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