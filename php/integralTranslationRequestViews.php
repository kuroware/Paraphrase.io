<?php
/*
This script integrals the views for all the translation request
 */
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Edit')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Edit.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
$mysqli = Database::connection();
$sql = "INSERT INTO `translation_request_views_integral` (translation_id, date, views)
SELECT t1.translation_id, CURDATE() as `date`, t1.views
FROM translations as t1
ON DUPLICATE KEY UPDATE views = t1.views";
$result = $mysqli->query($sql)
or die ($mysqli->error);
?>