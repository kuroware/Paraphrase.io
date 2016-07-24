<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/FusionChart.php';
$mysqli = Database::connection();
$sql = "SELECT languages.language_name, integral.value, DATE_FORMAT(integral.date, '%b %e, %Y') as `date`, integral.type
FROM integral
LEFT JOIN languages
ON languages.language_id = integral.language_id
ORDER BY integral.date ASC";
$result = $mysqli->query($sql)
or die ($mysqli->error);

$categories = array();
$keys = array('PHP', 'Javascript', 'C', 'Python', 'Ruby', 'Swift', 'Java', 'C++', 'C#', 'AngularJS'); //Keys for all the languages
$data_1 = array(
	0 => array(),
	1 => array(),
	2 => array(),
	3 => array(),
	4 => array(),
	5 => array(),
	6 => array(),
	7 => array(),
	8 => array(),
	9 => array()
);
$data_2 = $data_1; //To create a copy of the first array
$labels = array();

while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
$index_pos = array_search($row['language_name'], $keys); //The correct index position to insert this in
	if ($row['type'] == 1) {
		$data_1[$index_pos][] = $row['value'];
	}
	elseif ($row['type'] == 2) {
		$data_2[$index_pos][] = $row['value'];
	}
	if (!in_array($row['date'], $labels)) {
		$labels[] = $row['date'];
	}
}

echo json_encode(
	array(
		'data' => array($data_1, $data_2),
		'series' => $keys,
		'labels' => $labels
	), 
JSON_PRETTY_PRINT);
http_response_code(200);