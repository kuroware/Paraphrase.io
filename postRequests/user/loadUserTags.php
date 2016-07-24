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
require_once '../../vars/constants.php';
$request = file_get_contents('php://input');
$data = json_decode($request);
$profile_id = $data->profile_id;
/*$profile_id = 1;*/
try {
	if (is_numeric($profile_id)) {
		$mysqli = Database::connection();
		$sql = "
		SELECT t1.user_id, t2.to_language_id, t3.language_name, COUNT(t2.to_language_id) as answers
		FROM translation_results as t1
		LEFT JOIN translations as t2
		ON t2.translation_id = t1.translation_id
		LEFT JOIN languages as t3 
		ON t3.language_id = t2.to_language_id
		WHERE t1.user_id = '$profile_id'
		GROUP BY t2.to_language_id, t1.user_id
		ORDER BY answers DESC
		LIMIT 3
		";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if ($result->num_rows == 0) {
			$return_array = array();
		}
		else {
			$return_array = array();
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$x = array();
				$x['language'] = new Language(array(
					'language_id' => $row['to_language_id'],
					'language_name' => $row['language_name'])
				);
				$x['answers'] = $row['answers'];
				if ($classes[$x['language']->language_id]) {
					$x['class'] = 'label label-' . $classes[$x['language']->language_id];
				}
				else {
					$x['class'] = 'label label-default';
				}

				$return_array[] = $x;
			}
		}
		echo json_encode($return_array, JSON_PRETTY_PRINT);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException;
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}