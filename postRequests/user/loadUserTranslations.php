<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
//require_once '../../includes/database.php';
$user_id = $data->user_id;
/*$user_id = 2;*/
try {
	if (is_numeric($user_id)) {
		//require_once '../../includes/Language.php';
		$mysqli = Database::connection();
/*		require_once '../../includes/LanguageFunction.php';
		require_once'../../includes/Translate.php';*/
		$sql = "SELECT t1.translation_id, t1.result_id, t1.type, t1.upvotes, t1.downvotes, t1.comment, COALESCE(t2.suggested_method, t3.function_name) as function_name, COALESCE(t3.suggested_id, null) as function_id, t4.from_function_id as from_function_id, t4.from_function_name as from_function_name, t4.to_language_name, t4.to_language_id, t4.from_language_id, t5.language_name as from_language_name, t1.comment as comment, TIME_TO_SEC(TIMEDIFF(NOW(), t1.date_posted)) as `date_posted_ago`
		FROM translation_results as t1
		LEFT JOIN translation_multiple as t2
		ON t2.result_id = t1.result_id
		LEFT JOIN (
			SELECT t4.result_id, t4.suggested_id, t5.function_name as function_name
			FROM translation_singled as t4
			LEFT JOIN functions as t5
			ON t5.function_id = t4.suggested_id
		)as t3
		ON t3.result_id = t1.result_id
		LEFT JOIN (
			SELECT t1.translation_id, t1.from_function_id, t2.function_name as from_function_name, t2.language as from_language_id, t1.to_language_id, t3.language_name as to_language_name
			FROM translations as t1
			INNER JOIN functions as t2
			ON t2.function_id = t1.from_function_id
			INNER JOIN languages as t3
			ON t3.language_id = t1.to_language_id
		) as t4
		ON t4.translation_id = t1.translation_id
		LEFT JOIN languages as t5
		ON t5.language_id = t4.from_language_id
		WHERE t1.user_id ='$user_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$translations = array();
		while ($row = mysqli_fetch_array($result,  MYSQLI_ASSOC)) {
			$row['to_language'] = new Language(array(
				'language_id' => $row['to_language_id'],
				'language_name' => $row['to_language_name'])
			);
			$row['from_language'] = new Language(array(
				'language_id' => $row['from_language_id'],
				'language_name' => $row['from_language_name'])
			);

			$row['suggested_function'] = new LanguageFunction(array(
				'function_id' => $row['function_id'],
				'function_name' => $row['function_name'], 
				'function_language' => $row['to_language'])
			);

			$row['from_function'] = new LanguageFunction(array(
				'function_id' => $row['from_function_id'],
				'function_name' => $row['from_function_name'],
				'function_language' => $row['from_language'])
			);

			$translation = new OutgoingTranslation($row);
			$translation->date_posted_ago = Database::secondsToTime($row['date_posted_ago']);
			$formatted_string_date = '';
			if ($translation->date_posted_ago) {
				//echo $translation->action_date;
				//Attempt to style the action date
				$x = explode(', ', $translation->date_posted_ago);
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
				$translation->date_posted_ago = $formatted_string_date;
			}
			array_push($translations, $translation);
		}
		echo json_encode($translations, JSON_PRETTY_PRINT);
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