<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
header("Content-Type: application/json");
/*require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/Fusionchart.php';*/
$mysqli = Database::connection();
$sql = "SELECT languages.language_name, integral.value, integral.date, integral.type
FROM integral
LEFT JOIN languages
ON languages.language_id = integral.language_id
ORDER BY integral.date ASC";
$result = $mysqli->query($sql)
or die ($mysqli->error);

$categories = array();
$keys = array('PHP', 'Javascript', 'C', 'Python', 'Ruby', 'Swift', 'Java', 'C++', 'C#', 'AngularJS');
$dataset_type1 = array_fill_keys($keys, array());
$dataset_type2 = array_fill_keys($keys, array());

while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
	if ($row['type'] == 1) {
		$dataset_type1[$row['language_name']][] = $row['value'];
	}
	elseif ($row['type'] == 2) {
		$dataset_type2[$row['language_name']][] = $row['value'];
	}
	if (!in_array($row['date'], $categories)) {
		$categories[] = $row['date'];
	}
}

$x = new FusionChart(array(
	'categories' => $categories,
	'dataset' => $dataset_type1,
	'unique_series' => true,
	'chart_options' => array(
		'caption' => 'Indexed Functions',
		'xaxisname' => 'Date',
		'yaxisname' => 'Functions',
		'theme' => "ocean",
		'type' => 'line2d')
	)
);

$y = new FusionChart(array(
	'categories' => $categories,
	'dataset' => $dataset_type2,
	'unique_series' => true,
	'chart_options' => array(
		'caption' => 'Language Translation Popularity',
		'xaxisname' => 'Date',
		'yaxisname' => 'Translations To',
		'theme' => "ocean",
		'type' => 'line2d')
	)
);
echo json_encode(array($x, $y), JSON_PRETTY_PRINT);
http_response_code(200);