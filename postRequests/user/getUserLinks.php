<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Comment')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Comment.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$profile_id = $data->profile_id;
require_once '../../vars/constants.php';
//$profile_id = 2;
try {
	if (is_numeric($profile_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT t2.language_id as `from_language_id`, t2.language_name as `from_language_name`, t2.to_language_id as `to_language_id`, t3.language_name as `to_language_name`, COUNT(t1.result_id) as `answers`
		FROM translation_results as t1
		INNER JOIN (
			SELECT t2.language_id, t2.language_name, t1.translation_id, t1.to_language_id
			FROM translations as t1 
			LEFT JOIN (
				SELECT t1.function_id, t2.language_id, t2.language_name
				FROM functions as t1
				INNER JOIN languages as t2
				ON t2.language_id = t1.language
			) as t2
			ON t2.function_id = t1.from_function_id
		) as t2
		ON t2.translation_id = t1.translation_id
		INNER JOIN languages as t3
		ON t3.language_id = t2.to_language_id
		WHERE t1.user_id = '$profile_id'
		GROUP BY from_language_id, from_language_name, to_language_id, to_language_name
		ORDER BY answers DESC
		LIMIT 5";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$return_array = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			//Construct the languages
			$new_row = array();
			$new_row['to_language'] = new Language(array(
				'language_id' => $row['to_language_id'],
				'language_name' => $row['to_language_name'])
			);
			$new_row['from_language'] = new Language(array(
				'language_id' => $row['from_language_id'],
				'language_name' => $row['from_language_name'])
			);
			$new_row['answers'] = $row['answers'];
			if ($classes[$new_row['to_language']->language_id]) {
				$new_row['to_language']->class = 'label label-' . $classes[$new_row['to_language']->language_id];
			}
			else {
				$new_row['to_language']->class = 'label label-default';
			}
			if ($classes[$new_row['from_language']->language_id]) {
				$new_row['from_language']->class = 'label label-' . $classes[$new_row['from_language']->language_id];
			}
			else {
				$new_row['from_language']->class = 'label label-default';
			}
			$return_array[] = $new_row;
		}
		echo json_encode($return_array, JSON_PRETTY_PRINT);
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