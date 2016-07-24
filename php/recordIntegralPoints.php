<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
require_once '../includes/database.php';
$mysqli = Database::connection();
$sql = "SET @rank = 0;";
$result = $mysqli->query($sql)
or die ($mysqli->error);

$sql = "
INSERT INTO integral_users (ranking, user_id, points, date)
SELECT @rank:= @rank + 1 as rank, t1.user_id, t1.points, CURDATE()
FROM users as t1
ORDER BY t1.points DESC
ON DUPLICATE KEY UPDATE integral_id = integral_id";
$result = $mysqli->query($sql)
or die ($mysqli->error);
http_response_code(200);
?>
