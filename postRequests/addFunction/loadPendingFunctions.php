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
*/
$mysqli = Database::connection();
$sql = "
SELECT t1.function_id, t1.function_name, t1.syntax, t1.link, t1.description, t1.language, t2.language_name
FROM pending_functions as t1
LEFT JOIN languages as t2
ON t2.language_id = t1.language
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
	array_push($pending_functions, $function);
}
echo json_encode($pending_functions, JSON_PRETTY_PRINT);
http_response_code(200);
?>