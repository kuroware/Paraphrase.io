<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

require_once __DIR__ . '/../../includes/Database.php';
$mysqli = Database::connection();
$sql = "
SELECT COUNT(result_id) as `answers`, (
	SELECT COUNT(translation_id)
	FROM translations
) as `requests`
FROM translation_results";
$result = $mysqli->query($sql)
or die ($mysqli->error);
$row = mysqli_fetch_array($result);
echo json_encode($row, JSON_PRETTY_PRINT);
http_response_code(200);


