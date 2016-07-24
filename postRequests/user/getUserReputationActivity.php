<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$user_id = $data->user_id;
/*$user_id = 2;*/
try {
	if (is_numeric($user_id)) {
		$changes = array();
		$mysqli = Database::connection();
		$sql = "SELECT increment_by as `change`, type, user_id, linked_id, DATE_FORMAT(date, '%b %e, %Y') as `date_reputation`
		FROM `reputation_changes`
		WHERE user_id = '$user_id' 
		AND type NOT BETWEEN 7 AND 14
		AND type != 2 AND type !=4
		ORDER BY date DESC
		LIMIT 10";
		//Between clause due to the fact post and q and a functionality not implemented yet
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			if ($row['type'] == 5) {
				//The reputation change is due to a function being added
				$sql = "
				SELECT t1.function_id, t1.function_name, t1.language, t2.language_name
				FROM functions as t1 
				LEFT JOIN languages as t2 
				ON t2.language_id = t1.language
				WHERE t1.function_id = '" . $row['linked_id']. "'";
				$result_get = $mysqli->query($sql)
				or die ($mysqli->error);
				$row_info = mysqli_fetch_array($result_get, MYSQLI_ASSOC);
				$message = $row_info['language_name'] . "'s " . $row_info['function_name'];
				$change = new ReputationChange(array(
					'message' => $message,
					'linked_id' => $row['linked_id'],
					'type' => $row['type'],
					'change' => $row['change']
					)
				);
				if (!$changes[$row['date_reputation']]) {
					$changes[$row['date_reputation']] = array();
				}
				$changes[$row['date_reputation']][] = $change;
			}
			elseif (($row['type'] >= 1) && ($row['type'] <= 4)) {
				//The reputation change is due to upvoting the translation
				$sql = "
					SELECT t1.function_id as `from_function_id`, t1.function_name as `from_function_name`, t3.language_name `from_language_name`, t2.to_language_id, t4.language_name as `to_language_name`
					FROM functions as t1
					INNER JOIN (
						SELECT t2.from_function_id, t2.to_language_id
						FROM translation_results as t1
						INNER JOIN translations as t2 
						ON t2.translation_id = t1.translation_id
						WHERE t1.result_id = '" . $row['linked_id'] . "'
					) as t2
					ON t2.from_function_id = t1.function_id
					INNER JOIN languages as t3 
					ON t3.language_id = t1.language
					INNER JOIN languages as t4 
					ON t4.language_id = t2.to_language_id
				";
				$result_get = $mysqli->query($sql)
				or die ($mysqli->error);
				if ($result_get->num_rows == 1) {
					$row_info = mysqli_fetch_array($result_get, MYSQLI_ASSOC);
					$row['message'] = $row_info['from_language_name'] . "'s " . $row_info['from_function_name'] . " equivalent in " . $row_info['to_language_name'];
					$change = new ReputationChange($row);
					$change->result_spec = array($row_info['from_function_id'], $row_info['to_language_id']);
					if (!$changes[$row['date_reputation']]) {
						$changes[$row['date_reputation']] = array();
					}
					$changes[$row['date_reputation']][] = $change;
					//$changes[] = $change; //Push the reputation change into the array
				}
			}
			elseif (($row['type'] == 15) || ($row['type'] == 16)) {
				//Reputation change due to upvoted translation request
				$sql = "
				SELECT t2.language_name as `from_language_name`, t2.function_id as `from_function_id`, t2.function_name as `from_function_name`, t3.language_name as `to_language_name`, t3.language_id as `to_language_id`
				FROM translations as t1 
				INNER JOIN (
					SELECT t2.language_name, t1.function_id, t1.function_name
					FROM functions as t1
					INNER JOIN languages as t2 
					ON t2.language_id = t1.language
				) as t2
				ON t1.from_function_id = t2.function_id
				INNER JOIN languages as t3 
				ON t3.language_id = t1.to_language_id
				WHERE t1.translation_id = '" . $row['linked_id'] . "'";
				$result_get = $mysqli->query($sql)
				or die ($mysqli->error);
				if ($result_get->num_rows == 1) {
					$row_info = mysqli_fetch_array($result_get, MYSQLI_ASSOC);
					$row['message'] = $row_info['from_language_name'] . "'s " . $row_info['from_function_name'] . " equivalent in " . $row_info['to_language_name'];
					$change = new ReputationChange($row);
					$change->request_spec = array($row_info['from_function_id'], $row_info['to_language_id']);
					if (!$changes[$row['date_reputation']]) {
						$changes[$row['date_reputation']] = array();
					}
					$changes[$row['date_reputation']][] = $change;
					//$changes[] = $change; //Push the reputation change into the array
				}
			}
		}
		http_response_code(200);
		echo json_encode($changes, JSON_PRETTY_PRINT);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}