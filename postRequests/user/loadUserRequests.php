<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
/*require_once'../../includes/database.php';
require_once '../../includes/translate.php';
require_once '../../includes/Language.php';
require_once '../../includes/LanguageFunction.php';*/
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

$request = file_get_contents('php://input');
$data = json_decode($request);
$profile_id = $data->profile_id;
/*$profile_id = 2;*/

try {
	$mysqli = Database::connection();
	$mysqli->set_charset('utf8');
	if (is_numeric($profile_id)) {
		$sql = "SELECT t1.from_function_id, t5.language_name as from_language_name, t2.language as from_language_id, t2.function_name, t2.description, t1.to_language_id, t3.language_name as to_language, COALESCE(t4.answers, 0) as answers, TIME_TO_SEC(TIMEDIFF(NOW(), t1.date)) as `date_requested`, t1.views as `views`
		FROM translations as t1
		LEFT JOIN functions as t2
		ON t2.function_id = t1.from_function_id
		INNER JOIN languages as t3
		ON t3.language_id = t1.to_language_id
		INNER JOIN languages as t5
		ON t5.language_id = t2.language
		LEFT JOIN (
			SELECT translation_id, COUNT(result_id) as `answers`
			FROM translation_results
			GROUP BY translation_id
		) as t4
		ON t4.translation_id = t1.translation_id
		WHERE t1.user_id = '$profile_id'
		ORDER BY t1.date DESC";
/*		$sql = "SELECT t1.title, t1.body, t1.src_language, t1.des_language, t1.tagged_language
		FROM questions as t1
		LEFT JOIN users as t2 
		ON t2.user_id = t1.author_id
		WHERE t1.author_id = '$profile_id'";*/
		$result = $mysqli->query($sql)
		or die($mysqli->error);
		$translations = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$row['to_language'] = new Language(array(
				'language_id' => $row['to_language_id'],
				'language_name' => $row['to_language'])
			);
			$language = new Language(array(
				'language_id' => $row['from_language_id'],
				'language_name' => $row['from_language_name'])
			);
			$row['from_function'] = new LanguageFunction(array(
				'function_id' => $row['from_function_id'],
				'function_name' => $row['function_name'], 
				'description' => $row['description'],
				'function_language' => $language)
			);
			$translation = new OutgoingTranslation($row);
			$translation->date_requested = Database::secondsToTime($row['date_requested']);
			$formatted_string_date = '';
			if ($translation->date_requested) {
				//echo $translation->action_date;
				//Attempt to style the action date
				$x = explode(', ', $translation->date_requested);
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
				$translation->date_requested = $formatted_string_date;
			}
			$translation->views = $row['views'];
			$translation->answers = $row['answers'];
			$translations[] = $translation;
		}

		//-----Parsing for questions, not yet fully implemented yet--------------------------
/*		$sql = "SELECT t1.title, t1.body, t1.src_language, t1.des_language, t1.tagged_language, t2.username, t2.user_id
		FROM questions as t1
		LEFT JOIN users as t2 
		ON t2.user_id = t1.author_id
		WHERE t1.author_id = '$profile_id'";
		$result = $mysqli->query($sql)
		or die($mysqli->error);
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			if ($row['src_language']) {
				//Find the language names
				$sql = "SELECT language_name FROM languages WHERE language_id = '" . $row['src_language'] . "'";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				$src_language_name = mysqli_fetch_row($result)[0];
				$row['src_language'] = new Language(array(
					'language_id' => $row['src_language'],
					'language_name' => $src_language_name)
				);
				$sql = "SELECT language_name FROM languages WHERE language_id = '" . $row['des_language'] . "'";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				$des_langauge_name = mysqli_fetch_row($result)[0];
				$row['des_language'] = new Language(array(
					'language_id' => $row['des_language'],
					'langauge_name' => $row['des_language_name'])
				);
			}
			else {
				$row['tagged_language'] = new Language(array(
					'language_id' => $row['tagged_language'])
				);
			}
			$row['author'] = new User(array(
				'user_id' => $row['author_id'],
				'username' => $row['username'],
				'avatar' => $row['avatar'],
				'points' => $row['points'])
			);
			$question = new Question($row);
			$translations[] = $question;
		}*/
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