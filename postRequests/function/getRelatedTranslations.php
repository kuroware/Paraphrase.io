<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
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
$request = file_get_contents('php://input');
$data = json_decode($request);
$function_id = $data->function_id;
/*$function_id = 75;*/
try {
	if (is_numeric($function_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT t1.translation_id, t2.answers, t1.to_language_id as `language_id`, t3.language_name as `language_name`, t1.upvotes, t1.views, TIME_TO_SEC(TIMEDIFF(NOW(), t1.last_updated)) as `last_updated`
		FROM translations as t1
		LEFT JOIN (
			SELECT translation_id, COUNT(result_id) as `answers`
			FROM translation_results
			GROUP BY translation_id
		) as t2
		ON t2.translation_id = t1.translation_id
		INNER JOIN languages as t3
		ON t3.language_id = t1.to_language_id
		WHERE t1.from_function_id = '$function_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$translations = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			if (is_null($row['answers'])) {
				$to_language = new Language($row);
				$to_language->answers = 0;
			}
			else {
				$to_language = new Language($row);
				$to_language->answers = $row['answers'];
			}
			$to_language->upvotes = $row['upvotes'];
			$to_language->views = $row['views'];
			$translations[] = $to_language;
			$formatted_string_date = ''; //The temporary variable to hold the formatted string date
			if ($row['last_updated']) {
				//echo $this->action_date;
				//Attempt to style the action date
				$x = explode(', ', Database::secondsToTime($row['last_updated']));
				//print_r($x);
				foreach ($x as $key=>$val) {
					//echo $val;
					//Now go through and select the most relevant one, only adding the ones without 0 in it
					if (!strpos($val, 'minutes')) {
						//There are on minutes in this string so it can only be hours or days
						if (substr($val, 0, 1) != '0') {
							//echo 'found';
							$formatted_string_date .= $val;
							break; //The loop is done, grabbed the most relevant info
						}
					}
					else {
						//echo 'found';
						//Else this loop is minutes, which means the time can onnly be x minutes or x seconds
						$possible_time = explode('and ', $val);
						//print_r($possible_time);
						if (substr($val, 0, 1) == 0) {
							//Minutes is 0, only add seconds then
							$formatted_string_date .= $possible_time[1];
						}
						else {
							//Minutes is not 0, add the minutes
							$formatted_string_date .= $possible_time[0];
						}
					}
				}
				//Trim the string
				$formatted_string_date = trim($formatted_string_date);
				$to_language->last_updated = $formatted_string_date;
			}
		}
		echo json_encode($translations, JSON_PRETTY_PRINT);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);
}