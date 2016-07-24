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
$translation_id = $data->translation_id;
//$translation_id = 1;
$user_id = User::get_current_user_id();
try {
	if (is_numeric($translation_id)) {
		$mysqli = Database::connection();
		if (is_numeric($user_id)) {
			$sql = "SELECT t1.upvotes, t1.views, COALESCE(t2.answers, 0) as `answers`, t3.status, DATE_FORMAT(t1.date, '%b %e, %Y') as `first_asked`, t1.last_updated as `last_updated`
			FROM translations as t1 
			LEFT JOIN (
				SELECT translation_id, COUNT(result_id) as `answers`
				FROM translation_results
				GROUP BY translation_id
			) as t2
			ON t2.translation_id = t1.translation_id
			LEFT JOIN `translation_requests_feedback` as t3 
			ON t3.translation_id = t1.translation_id
			AND t3.user_id = '$user_id'
			WHERE t1.translation_id = '$translation_id'";
		}
		else {
			$sql = "SELECT t1.upvotes, t1.views, COALESCE(t2.answers, 0) as `answers`, null as `status`, DATE_FORMAT(t1.date, '%b %e, %Y') as `first_asked`, t1.last_updated as `last_updated`
			FROM translations as t1 
			LEFT JOIN (
				SELECT translation_id, COUNT(result_id) as `answers`
				FROM translation_results
				GROUP BY translation_id
			) as t2
			ON t2.translation_id = t1.translation_id
			WHERE t1.translation_id = '$translation_id'";
		}
		//echo $sql;
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if ($result->num_rows == 1) {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC); //The query information
			$row['contributors'] = array(); //The holding array for all the contributors

			if (is_null($row['status'])) {
				$row['upvoted'] = false;
			}
			else {
				$row['upvoted'] = true;
			}

			//Now get all the contributors who answers this question
			$sql = "SELECT DISTINCT(t2.user_id), t2.avatar, t2.points, t2.username, DATE_FORMAT(t1.date_posted, '%b %e, %Y') as `date_posted`, t1.upvotes, t1.downvotes
			FROM translation_results as t1 
			LEFT JOIN users as t2
			ON t2.user_id = t1.user_id
			WHERE t1.translation_id = '$translation_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			while ($row_contributor = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$contributor = new User($row_contributor);
				$contributor->date_posted = $row_contributor['date_posted'];
				$contributor->upvotes = $row_contributor['upvotes'];
				$contributor->downvotes = $row_contributor['downvotes'];
				$row['contributors'][] = $contributor;
			}

			echo json_encode($row, JSON_PRETTY_PRINT);
			http_response_code(200);
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);
}
catch (OutOfRangeException $e) {
	http_response_code(400);
	Database::print_exception($e);
}