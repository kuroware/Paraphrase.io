<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

header("Content-Type: application/json"); //Set header for outputing the JSON information
/*require_once '../../includes/database.php';
require_once '../../includes/LanguageFunction.php';
require_once '../../includes/Language.php';
require_once '../../includes/User.php';*/
require_once '../../vars/constants.php';
$request = file_get_contents('php://input');
$data = json_decode($request);
$user_id = $data->user_id;
/*$user_id = 2;*/
try {
	if (is_numeric($user_id)) {
		$mysqli = Database::connection();
		$mysqli->set_charset('utf8');
		$sql = "
		SELECT t1.function_id, t1.function_name, t1.syntax, t1.link, t1.description, t1.language, t2.language_name
		FROM functions as t1
		LEFT JOIN languages as t2
		ON t2.language_id = t1.language
		WHERE t1.user_id = '$user_id'
		ORDER BY t2.language_name ASC
		";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$pending_functions = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$row['function_language'] = new Language(array(
				'language_id' => $row['language'],
				'language_name' => $row['language_name'])
			);

			$function = new LanguageFunction($row);
			$function->class = $classes[$row['language']];

			array_push($pending_functions, $function);
		}
		echo json_encode($pending_functions, JSON_PRETTY_PRINT);
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


?>