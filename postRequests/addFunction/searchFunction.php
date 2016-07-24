<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json"); //Set header for outputing the JSON information
$function = $_GET['function'];
/*require_once '../../includes/database.php';
require_once '../../includes/LanguageFunction.php';
require_once '../../includes/Language.php';*/
try {
	if ($function) {
		$mysqli = Database::connection();
		$function = Database::sanitize(trim($function, '.'));
		$function = trim($function, '()'); //Just search for the base function

		$sql = "SELECT t1.function_id, t1.function_name, t1.language, t1.category_id, t2.language_name, t1.description
		FROM functions as t1
		LEFT JOIN languages as t2
		ON t2.language_id = t1.language 
		WHERE t1.function_name LIKE '$function%' OR t1.function_name LIKE '%.$function%'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		$functions = array();

		while ($row = mysqli_fetch_array($result)) {
			$row['function_language'] = new Language(array(
				'language_id' => $row['language'],
				'language_name' => $row['language_name'])
			);
			$function = new LanguageFunction($row);
			$function->title = $function->function_language->language_name . ' - ' . $function->function_name;
			$function->summary = substr($function->description, 0, 75) . '...';
			array_push($functions, $function);
		}
		echo json_encode($functions, JSON_PRETTY_PRINT);
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