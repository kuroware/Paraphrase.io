<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

/*require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Translate.php';
require_once __DIR__ . '/../../includes/Language.php';
require_once __DIR__ . '/../../includes/LanguageFunction.php';
require_once __DIR__ . '/../../includes/User.php';*/

$user_id = User::get_current_user_id();

$request = file_get_contents('php://input');
$data = json_decode($request);
$filter = $data->filter;
$to = $data->filter;
/*$filter = 'all';*/

try {
	$mysqli = Database::connection();
	$keys = range(1, 11);
	if (in_array($filter, $keys)) {
		if ($filter == 11) {
			$sql = "SELECT t1.from_function_id, t5.language_name as from_language_name, t2.language as from_language_id, t2.function_name, t2.description, t1.to_language_id, t3.language_name as to_language, COALESCE(t4.answers, 0) as answers, t1.upvotes as upvotes, t1.translation_id, t6.feedback_id as feedback, t1.views, t8.username, t8.avatar, t8.points, t8.user_id, t1.linked_id, t1.action_type as `identifier`, TIME_TO_SEC(TIMEDIFF(NOW(), t1.last_updated)) as `action_date`
			FROM translations as t1
			INNER JOIN functions as t2
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
			LEFT JOIN translation_requests_feedback as t6
			ON t6.translation_id = t1.translation_id
			AND t6.user_id = '$user_id'
			LEFT JOIN users as t8 
			ON t8.user_id = t1.user_id
			WHERE t1.date
			BETWEEN DATE_ADD(CURDATE(), INTERVAL 1-DAYOFWEEK(CURDATE()) DAY)
			AND DATE_ADD(CURDATE(), INTERVAL 7-DAYOFWEEK(CURDATE()) DAY)
			ORDER BY answers DESC, t1.views DESC, t1.date DESC";
		}
		else {
			$language_id = array_search($filter, $keys);
			if ($to) {
				$sql = "SELECT t1.from_function_id, t5.language_name as from_language_name, t2.language as from_language_id, t2.function_name, t2.description, t1.to_language_id, t3.language_name as to_language, COALESCE(t4.answers, 0) as answers, t1.upvotes as upvotes, t6.feedback_id as feedback, t1.views, t8.username, t8.avatar, t8.points, t8.user_id, t1.linked_id, t1.action_type as `identifier`, TIME_TO_SEC(TIMEDIFF(NOW(), t1.last_updated)) as `action_date`
				FROM translations as t1
				INNER JOIN functions as t2
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
				LEFT JOIN translation_requests_feedback as t6
				ON t6.translation_id = t1.translation_id
				AND t6.user_id = '$user_id'
				LEFT JOIN users as t8 
				ON t8.user_id = t1.user_id
				WHERE t1.date
				BETWEEN DATE_ADD(CURDATE(), INTERVAL 1-DAYOFWEEK(CURDATE()) DAY)
				AND DATE_ADD(CURDATE(), INTERVAL 7-DAYOFWEEK(CURDATE()) DAY)
				AND t1.to_language_id = '$filter'
				ORDER BY answers DESC, t1.views DESC, t1.date DESC";
			}
			else {
				$sql = "SELECT t1.from_function_id, t5.language_name as from_language_name, t2.language as from_language_id, t2.function_name, t2.description, t1.to_language_id, t3.language_name as to_language, COALESCE(t4.answers, 0) as answers, t1.upvotes as upvotes, t6.feedback_id as feedback, t1.views, t8.username, t8.avatar, t8.points, t8.user_id, t1.linked_id, t1.action_type as `identifier`, TIME_TO_SEC(TIMEDIFF(NOW(), t1.last_updated)) as `action_date`
				FROM translations as t1
				INNER JOIN functions as t2
				ON t2.function_id = t1.from_function_id
				AND t2.language = '$filter'
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
				LEFT JOIN translation_requests_feedback as t6
				ON t6.translation_id = t1.translation_id
				AND t6.user_id = '$user_id'
				LEFT JOIN users as t8 
				ON t8.user_id = t1.user_id
				WHERE t1.date
				BETWEEN DATE_ADD(CURDATE(), INTERVAL 1-DAYOFWEEK(CURDATE()) DAY)
				AND DATE_ADD(CURDATE(), INTERVAL 7-DAYOFWEEK(CURDATE()) DAY)
				ORDER BY answers DESC, t1.views DESC, t1.date DESC";
			}
		}
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
			if (!is_null($row['feedback'])) {
				$translation->upvoted = true;
			}
			else {
				$translation->upvoted = false;
			}
			$translation->answers = $row['answers'];
			$translation->views = $row['views'];
			$author = new User(array(
				'user_id' => $row['user_id'],
				'username' => $row['username'],
				'avatar' =>  $row['avatar'],
				'points' => $row['points'])
			);
			$translation->asker = $author;
			//Create the last activity
			$last_activity = new TranslationActivity(array(
				'action_date' => $row['action_date'],
				'linked_id' => $row['linked_id'],
				'identifier' => $row['identifier'])
			);
			$translation->last_activity = $last_activity;
			$translation->resolve_activity_message();
			$translations[] = $translation;
		}
		echo json_encode($translations, JSON_PRETTY_PRINT);
		http_response_code(200);
	}
	else {
		throw new OutOfRangeException('OutOfRangeException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}