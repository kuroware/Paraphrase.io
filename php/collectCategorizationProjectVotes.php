<?php
//Updates all the functions with the most voted category
$mysqli = Database::connection();
$sql = "
INSERT INTO functions (function_id, category_id)
SELECT t1.function_id, t2.category_id
FROM (
	SELECT function_id, category_id, COUNT(user_id)
	FROM categorization_project
	GROUP BY function_id, category_id
) as t1
INNER JOIN (
	SELECT category_id, MAX(user_id) as `votes_to`
	FROM (
		SELECT function_id, category_id, COUNT(user_id) as `user_id`
		FROM categorization_project
		GROUP BY function_id, category_id
	) as t
	GROUP BY category_id
) as t2
ON t2.category_id = t1.category_id
ON DUPLICATE KEY UPDATE category_id = t2.category_id
";
$result = $mysqli->query($sql)
or die ($mysqli->error);
