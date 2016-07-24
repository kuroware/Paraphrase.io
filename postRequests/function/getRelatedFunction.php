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
/*require_once '../../includes/database.php';
require_once '../../includes/LanguageFunction.php';
require_once '../../includes/Language.php';
*/$data = json_decode($request);
$super_id = $data->super_id;
$language = $data->language;
/*$language = 1;
$super_id = 4;*/
try {
	if (is_numeric($language) && is_numeric($super_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT t1.function_id, t1.function_name, COALESCE(t2.counter, 0) as counter, t5.language_name, t1.language as language_id, t6.super_id, t6.super_description
		FROM functions as t1
		LEFT JOIN (
			SELECT t3.from_function_id, COALESCE(t4.counter, 0) as counter
			FROM translations as t3
			LEFT JOIN (
				SELECT translation_id, COUNT(translation_id) as counter
				FROM translation_results
				GROUP BY translation_id
			) as t4
			ON t3.translation_id = t4.translation_id
			GROUP BY t3.from_function_id
		) as t2
		ON t2.from_function_id = t1.function_id
		LEFT JOIN languages as t5
		ON t5.language_id = t1.language
		LEFT JOIN (
			SELECT t1.category_id, t2.super_id as super_id, t2.description as super_description
			FROM category as t1
			LEFT JOIN super_category as t2
			ON t2.super_id = t1.type
		) as t6
		ON t6.category_id = t1.category_id
		WHERE t1.language = '$language'
		ORDER BY
			CASE t6.super_id
				WHEN '$super_id' THEN 1
				ELSE 0 END
			DESC, 
			t1.function_name ASC
		LIMIT 10";

		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$functions = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$row['function_language'] = new Language(array(
				'language_id' => $row['language_id'],
				'language_name' => $row['language_name'])
			);
			$function = new LanguageFunction(array(
				'function_id' => $row['function_id'],
				'function_name' => $row['function_name'],
				'function_language' => $row['function_language'])
			);
			$function->counter = $row['counter'];
			$functions[] = $function;
		}
		echo json_encode($functions, JSON_PRETTY_PRINT);	
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}

catch (UnexpectedValueException $e) {
	Database::print_excpetion($e);
	http_response_code(400);
}
