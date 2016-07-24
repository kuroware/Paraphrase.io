<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$language_id = $data->language_id;
/*$language_id = 1;*/
try {
	if (is_numeric($language_id)) {
		$mysqli = Database::connection();
		$sql = "
		SELECT t1.to_language_id, COUNT(t2.result_id) as `results`
		FROM translations as t1
		LEFT JOIN translation_results as t2 
		ON t2.translation_id = t1.translation_id
		GROUP BY t1.to_language_id
		ORDER BY results DESC
		";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$rank = 1;
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			if ($row['to_language_id'] == $language_id) {
				break;
			}
			$rank++;
		}
		$sql = "
		SELECT t1.to_language_id, COUNT(t2.result_id) as `results`
		FROM translations as t1
		LEFT JOIN translation_results as t2 
		ON t2.translation_id = t1.translation_id
		WHERE t1.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND CURDATE()
		GROUP BY t1.to_language_id
		ORDER BY results DESC
		";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$rank_this_week = 1;
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			if ($row['to_language_id'] == $language_id) {
				break;
			}
			$rank_this_week++;
		}
		$array = array('lifetime_rank' => $rank, 'weekly_rank' => $rank_this_week);
		http_response_code(200);
		echo json_encode($array, JSON_PRETTY_PRINT);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);
}
?>