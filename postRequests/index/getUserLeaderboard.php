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
/*require_once __DIR__ . '/../../includes/user.php';
require_once __DIR__ . '/../../includes/database.php';*/
$request = file_get_contents('php://input');
$data = json_decode($request);
$filter = $data->filter;
$filter = 11;

$mysqli = Database::connection();
try {
	$keys = range(1, 11);
	if (in_array($filter, $keys)) {
		if ($filter == 11) {
			$sql = "SELECT t1.user_id, t1.username, t1.points, t1.avatar, t2.answers as `language_answers`, t2.language_id, t2.language_name as `top_category`, t2.total_answers
			FROM users as t1
			LEFT JOIN (
				SELECT t1.user_id, t1.language_id, t1.answers, t2.language_name, t3.total_answers
				FROM (
				  SELECT user_id, language_id, answers,
				         @rn:= IF(@uid = user_id,
				                  IF(@uid:=user_id, @rn:=@rn+1, @rn:=@rn+1),
				                  IF(@uid:=user_id, @rn:=1, @rn:=1)) AS rn
				  FROM (SELECT t1.user_id, t2.to_language_id AS language_id, 
				               COUNT(t2.to_language_id) as answers     
				        FROM translation_results as t1 
				        LEFT JOIN translations as t2 
				           ON t2.translation_id = t1.translation_id
				        GROUP BY t2.to_language_id, t1.user_id 
				       ) t
				  CROSS JOIN (SELECT @rn:=0, @uid:=0) AS vars
				  ORDER BY user_id, answers DESC
				) as t1
				LEFT JOIN languages as t2
				ON t2.language_id = t1.language_id
				LEFT JOIN (
					SELECT user_id, COUNT(result_id) as `total_answers`
					FROM translation_results
					GROUP BY user_id
				) as t3
				ON t3.user_id = t1.user_id
				WHERE t1.rn = 1
			) as t2
			ON t2.user_id = t1.user_id
			ORDER BY t1.points DESC
			LIMIT 10";
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
	$result = $mysqli->query($sql)
	or die ($mysqli->error);
	$users = array();
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$user = new User($row);
		$user->language_answers = $row['language_answers'];
		$user->total_answers = $row['total_answers'];
		$user->top_category = $row['top_category'];
		$users[] = $user;
	}
	echo json_encode($users, JSON_PRETTY_PRINT);
	http_response_code(200);
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}

?>