<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$question_id = $data->question_id;
$user_id = User::get_current_user_id();
/*$question_id = 3;*/
try {
	if (is_numeric($question_id)) {
		$mysqli = Database::connection();
		if (is_numeric($user_id)) {
			$sql = "SELECT t1.question_id, t1.title, t1.body, TIME_TO_SEC(TIMEDIFF(NOW(), t1.date_posted)) as `date_posted_ago`, t1.src_language, t1.des_language, t1.tagged_language, t1.author_id, t2.username, t2.avatar, t2.points, t3.status, t1.upvotes, t1.downvotes
			FROM questions as t1
			LEFT JOIN users as t2
			ON t2.user_id = t1.author_id
			LEFT JOIN question_feedback as t3 
			ON t3.question_id = t1.question_id
			AND t3.user_id = '$user_id'
			WHERE t1.question_id = '$question_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
		}
		else {
			$sql = "SELECT t1.question_id, t1.title, t1.body, TIME_TO_SEC(TIMEDIFF(NOW(), t1.date_posted)) as `date_posted_ago`, t1.src_language, t1.des_language, t1.tagged_language, t1.author_id, t2.username, t2.avatar, t2.points, null as `status`, t1.upvotes, t1.downvotes
			FROM questions as t1
			LEFT JOIN users as t2
			ON t2.user_id = t1.author_id
			WHERE t1.question_id = '$question_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
		}
		if ($result->num_rows == 1) {
			$row = mysqli_fetch_array($result);
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
			echo json_encode($question, JSON_PRETTY_PRINT);
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
	Database::print_exception($e);
	http_response_code(400);
}
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
?>