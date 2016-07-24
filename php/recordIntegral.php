<?php
require_once '../includes/database.php';
require_once '../vars/constants.php';



$mysqli = Database::connection();

$sql = "INSERT INTO integral (language_id, value, date, type)
SELECT language, COUNT(function_id), CURDATE(), " . NUMBER_OF_FUNCTIONS_TYPE . "
FROM functions
GROUP BY language
ON DUPLICATE KEY UPDATE integral_id = integral_id";
$result = $mysqli->query($sql)
or die ($mysqli->error);

$sql = "INSERT INTO integral (language_id, value, date, type)
SELECT to_language_id, COUNT(translation_id), CURDATE(), " . LANGUAGE_TRANSLATIONS_TYPE . "
FROM translations
GROUP BY to_language_id
ON DUPLICATE KEY UPDATE integral_id = integral_id";
$result = $mysqli->query($sql)
or die ($mysqli->error);

$sql = "INSERT INTO total_points_integral (date, value) 
SELECT CURDATE(), SUM(points) FROM users";
$result = $mysqli->query($sql)
or die ($mysqli->error);
?>