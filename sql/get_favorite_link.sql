SELECT t2.language_id as `from_language_id`, t2.language_name as `from_language_name`, t2.to_language_id as `to_language_id`, t3.language_name as `to_language_name`, COUNT(t1.result_id) as `answers`
FROM translation_results as t1
INNER JOIN (
	SELECT t2.language_id, t2.language_name, t1.translation_id, t1.to_language_id
	FROM translations as t1 
	LEFT JOIN (
		SELECT t1.function_id, t2.language_id, t2.language_name
		FROM functions as t1
		INNER JOIN languages as t2
		ON t2.language_id = t1.language
	) as t2
	ON t2.function_id = t1.from_function_id
) as t2
ON t2.translation_id = t1.translation_id
INNER JOIN languages as t3
ON t3.language_id = t2.to_language_id
WHERE t1.user_id = 2
GROUP BY from_language_id, from_language_name, to_language_id, to_language_name