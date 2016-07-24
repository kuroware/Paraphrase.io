<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

error_reporting(E_ERROR);
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$language_id = $data->language_id;
$function_name = $data->function_name;
$syntax = $data->syntax;
$link = $data->link;
$summary = $data->summary;
$category_id = $data->category_id;
$new_category = $data->new_category;
$super_category = $data->super_id;
/*$link = 'https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/split';
$syntax = 'str.split([separator[, limit]])';
$function_name = 'str.split';
$summary = 'The split() method splits a String object into an array of strings by separating the string into substrings.';
$language_id = 2;
$category_id = 2;*/
/*require_once '../../includes/database.php';*/
/*require_once '/../../vars/constants.php';*/
/*require_once '../../includes/user.php';
*/
/*$language_id = 1;
$new_category = 'Counts the frequency of all values in an array';
$function_name = 'array_key_existddsdddd()';
$super_category = 2;
*/
$user_id = (User::get_current_user_id() == 'None') ? ANONYMOUS_USER_ID : User::get_current_user_id();

try {
	if (is_numeric($language_id) && $function_name && ($category_id XOR $new_category)) {
		$mysqli = Database::connection();

		$function_name = Database::sanitize($function_name);
		$syntax = Database::sanitize($syntax);
		$link = Database::sanitize($link);
		$summary = strip_tags(Database::sanitize($summary), '<strong><code><hr><em><i><u>');

		if ($new_category) {
			//User has requested a new category to be added
			$new_category = Database::sanitize($new_category);
			$insert = "INSERT category (description, type) VALUES ('$new_category', '$super_category')
			ON DUPLICATE KEY UPDATE category_id = category_id";
			$result = $mysqli->query($insert)
			or die ($mysqli->error);
			$category_id = $mysqli->insert_id;
		}

		$sql = "INSERT INTO pending_functions (function_name, category_id, syntax, language, description, link, user_id) VALUES ('$function_name','$category_id', '$syntax', '$language_id', '$summary', '$link', '$user_id')
		ON DUPLICATE KEY UPDATE category_id = '$category_id', syntax = '$syntax', description = '$summary', language = '$language_id', link = '$link'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if ($mysqli->affected_rows == 1 && $user_id != 3) {
			//Not a duplicate entry, award reputation points to user
			$user = new User(array(
				'user_id' => $user_id)
			);
			$function_id = $mysqli->insert_id;
			$user->change_reputation(5, 5, $function_id);			
		}
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
?>