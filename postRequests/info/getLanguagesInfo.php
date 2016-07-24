<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
require_once '../../includes/Database.php';
require_once '../../includes/Language.php';

$output_array = array('translations_to' => array(), 'translations_from' => array());

$mysqli = Database::connection();
$sql = "SELECT to_language_id as `language_id`, COUNT(translation_id) as `translations_to`
FROM translations
GROUP BY to_language_id";
$result = $mysqli->query($sql)
or die ($mysqli->error);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$output_array['translations_to'][$row['language_id']] = $row['translations_to'];
}

//Selection of count based on how many translations are from this language
$sql = "SELECT t2.language, COUNT(t1.translation_id) as `translations_from`
FROM translations as t1
LEFT JOIN functions as t2 
ON t2.function_id = t1.from_function_id
GROUP BY t2.language
";
$result = $mysqli->query($sql)
or die ($mysqli->error);
while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	$output_array['translations_from'][$row['language']] = $row['translations_from'];
}
$sql = "SELECT COUNT(translation_id) FROM translations";
$result = $mysqli->query($sql)
or die ($mysqli->error);
$total = mysqli_fetch_row($result)[0];
$output_array['translations_to'][11] = $total;
$output_array['translations_from'][11] = $total;
echo json_encode($output_array, JSON_PRETTY_PRINT);
http_response_code(200);
