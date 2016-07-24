<?php
require_once '../includes/database.php';
$mysqli = Database::connection();

$sql = "SELECT profile_id, COUNT(ip_address)
FROM temp_profile_views
GROUP BY profile_id";
$result = $mysqli->query($sql)
or die ($mysqli->error);

//Update the profile views
while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
	$sql = "UPDATE users SET views = views + $row[1] WHERE user_id = '$row[0]'";
	$result_update = $mysqli->query($sql)
	or die ($mysqli->error);
}

//Clear the data
$sql = "TRUNCATE temp_profile_views";
$result = $mysqli->query($sql)
or die ($mysqli->error);
