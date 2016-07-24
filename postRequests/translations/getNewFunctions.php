<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
/*require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/LanguageFunction.php';
require_once __DIR__ . '/../../includes/Language.php';*/
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}


$mysqli = Database::connection();

$sql = "SELECT t1.function_id, t1.function_name, COALESCE(t2.counter, 0) as counter, t5.language_name, t1.language as language_id
FROM functions as t1
LEFT JOIN (
	SELECT t2.from_function_id, COUNT(t1.result_id) as `counter`
	FROM translation_results as t1 
	LEFT JOIN translations as t2
	ON t2.translation_id = t1.translation_id
	GROUP BY t2.from_function_id	
) as t2
ON t2.from_function_id = t1.function_id
LEFT JOIN languages as t5
ON t5.language_id = t1.language
ORDER BY t1.date DESC
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