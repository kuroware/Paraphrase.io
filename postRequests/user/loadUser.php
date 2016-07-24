<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
//require_once '../../includes/database.php';
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

$user_id = $data->user_id;
/*$user_id = 2;*/
try {
	if (is_numeric($user_id)) {
		//require_once '../../includes/user.php';
		$mysqli = Database::connection();
		$sql = "SELECT user_id, username, avatar, description, points, DATE_FORMAT(date_joined, '%b %e, %Y') as date_joined, views, email, location, se, github, TIME_TO_SEC(TIMEDIFF(NOW(), last_logged_in)) as last_logged_in_ago
		FROM users
		WHERE user_id = '$user_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if ($result->num_rows == 1) {
			$row = mysqli_fetch_array($result);
			$sql = "SELECT COUNT(t1.result_id) as `answers`, (SELECT COUNT(translation_id) FROM translations WHERE user_id = '$user_id') as `requests`, SUM(t1.upvotes) as `upvotes`, SUM(t1.downvotes) as `downvotes`, (t1.upvotes / GREATEST(t1.downvotes, 1)) as `ud_ratio`, COALESCE((SELECT points FROM integral_users WHERE user_id = '$user_id' AND date = DATE_SUB(CURDATE(), INTERVAL 7 DAY)), 1) as `last_week`
				FROM translation_results as t1
				WHERE t1.user_id = '$user_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$row_1 = mysqli_fetch_array($result);

			//Get the user comments
			$sql_comments = "SELECT t1.comment_id, t1.comment_text, DATE_FORMAT(t1.date_posted, '%b %e, %Y') as date_posted, t1.author_id, t2.username, t2.avatar, t2.points FROM `profile_comments` as t1 
			LEFT JOIN users as t2 
			ON t2.user_id = t1.author_id
			WHERE t1.profile_id = '$user_id'
			ORDER BY t1.date_posted DESC";
			$result_comments = $mysqli->query($sql_comments)
			or die ($mysqli->error);

			$comments = array('comments' => array());
			while ($row_comment = mysqli_fetch_array($result_comments)) {
				$row_comment['author'] = new User(array(
					'user_id' => $row_comment['author_id'],
					'username' => $row_comment['username'],
					'avatar' => $row_comment['avatar'],
					'points' => $row_comment['points'])
				);
				$comment = new ProfileComment($row_comment);
				$comments['comments'][] = $comment;
			}	

			$row = array_merge($row, $row_1, $comments);

			$user = new User($row);
			$user->log_profile_view();

			$user->gained_week = $row['points'] - $row['last_week'];
			$user->this_week = round((((intval($row['points']) - intval($row['last_week'])) / intval($row['last_week'])) * 100), 2);

			echo json_encode($user, JSON_PRETTY_PRINT); //Print the user
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