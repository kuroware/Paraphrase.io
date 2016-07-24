<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
require_once __DIR__ . '/../../includes/user.php';
require_once __DIR__ . '/../../includes/database.php';
$request = file_get_contents('php://input');
$data = json_decode($request);
$filter = $data->filter;
/*$filter = 'all';*/

$mysqli = Database::connection();
try {
	switch ($filter) {
		case 'all':
			$sql = "SELECT t1.user_id, t1.username, t1.points, t1.avatar, t2.current_ranking, t2.previous_day_ranking, t2.previous_week_ranking, IF(t2.previous_day_ranking != 0, -1*(t2.current_ranking - t2.previous_day_ranking), t2.current_ranking - t2.previous_day_ranking) as daily_ranking_change, IF(t2.previous_week_ranking != 0, -1*(t2.current_ranking - t2.previous_week_ranking), t2.current_ranking - t2.previous_week_ranking) as weekly_ranking_change, ROUND((((t1.points - t2.previous_week_points) / t2.previous_week_points) * 100), 2) as weekly_point_percentage_change
			FROM users as t1
			LEFT JOIN (
				SELECT t1.ranking as current_ranking, t1.user_id, COALESCE(t2.ranking, 0) as previous_week_ranking, COALESCE(t3.ranking, 0) as previous_day_ranking, COALESCE(t2.points, 1) as previous_week_points
				FROM integral_users as t1
				LEFT JOIN (
					SELECT ranking, user_id, points
					FROM integral_users
					WHERE date = DATE_SUB(CURDATE(), INTERVAL 1 WEEK)
				) as t2
				ON t2.user_id = t1.user_id
				LEFT JOIN (
					SELECT ranking, user_id
					FROM integral_users
					WHERE date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
				) as t3 
				ON t3.user_id = t1.user_id
				WHERE date = (
					SELECT MAX(date)
					FROM integral_users
				)
			) as t2
			ON t2.user_id = t1.user_id
			WHERE t1.user_id != 3
			ORDER BY t2.current_ranking ASC
			";
			break;
		default:
			throw new UnexpectedValueException('UnexpectedValueException occured on request');
			break;
	}
	$result = $mysqli->query($sql)
	or die ($mysqli->error);
	$users = array();
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$user = new User($row);
		$user->weekly_point_percentage_change = $row['weekly_point_percentage_change'];
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