<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
require_once '../../includes/database.php';
$stats = Database::pull_search_stats_today();
echo json_encode($stats, JSON_PRETTY_PRINT);
http_response_code(200);
?>