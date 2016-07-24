<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}

header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$comment = $data->comment;
$translation_id = $data->translation_id; //The translation id
$translation = $data->translation;
$single_function = $data->single_function; //If this is ture, the database will try to track down which function it is and provide a more inuitive answer by including its parameters
$linked_function_id = $data->linked_function_id;
$note = $data->type == 2;
/*require_once '../../includes/Database.php';
require_once '../../includes/user.php';
require_once '../../includes/TranslationActivity.php';
require_once '../../includes/Translate.php';*/
require_once '../../vars/constants.php';

/*$comment = 'The Array.prototype.pop() method, or .pop() method that comes built in with any array in Javascript pops and remove the last element of the array and returns it. It affects the object itself, it does not return the modified array. ';
$translation_id = 84;
$translation = "Array.prototype.pop()

someArray = ['value1', 'value2']
poppedElement = someArray.pop()
console.log(someArray) //Outputs ['value1']
console.log(poppedElement) //Outputs 'value2'";*/
/*$single_function = false;
$note = '2' == 1;*/
 	

try {
	if (($comment && (strlen($comment) > 10) && is_numeric($translation_id)) || ($single_function && is_numeric($linked_function_id))) {
		$user_id = User::get_current_user_id();
		$translation = Database::sanitize($translation);
		$user_id = (is_numeric($user_id)) ? $user_id : ANONYMOUS_USER_ID; //Anonymous user
		//$user_id = 2; //For now
		//The string is the appropiate length, parse the translation
		require_once '../../vars/constants.php';
		require_once '../../includes/LanguageFunction.php';

		$mysqli = Database::connection(); //Connection variable

		if ($single_function) {
			//Attempt to find the function, if it doesn't exist insert it
			$find = trim(Database::sanitize($translation), '.');
			$sql = "SELECT function_id, function_name FROM functions WHERE function_id = '$linked_function_id'
			";
			$result = $mysqli->query($sql)
			or die($mysqli->error);
			if ($result->num_rows == 1) {
				$row = mysqli_fetch_row($result);
				$suggested_function = new LanguageFunction(array(
					'function_id' => $row[0], 
					'function_name' => $row[1])
				);
			}
			else {
				//Add it since it doesnt exist yet as a new function
				$sql = "SELECT t1.to_language_id, t2.category_id
				FROM translations as t1
				LEFT JOIN (
					SELECT category_id, function_id
					FROM functions
				) as t2
				ON t2.function_id = t1.from_function_id
				WHERE t1.translation_id = '$translation_id' 
				";
/*				$sql = "SELECT t1.category_id, t2.to_language_id	
				FROM functions as t1
				INNER JOIN translations as t2
				ON t2.from_function_id = t1.function_id
				AND t2.translation_id = '$translation_id'
				";*/
				$result = $mysqli->query($sql)
				or die ($mysqli->error);

				$row = mysqli_fetch_array($result, MYSQLI_NUM);
				list($lan_id, $cat) = $row;
				//Insert it into funcitons
				$sql = "INSERT INTO functions (function_name, category_id, language, date) VALUES ('$translation','$cat', '$lan_id', NOW())
				ON DUPLICATE KEY UPDATE function_id = function_id";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);

				$id = $mysqli->insert_id;
				if ($mysqli->affected_rows == 1 && $user_id) {
					$user = new User(array(
						'user_id' => $user_id)
					);
					$user->change_reputation(5, 5, $id);
				}
				$suggested_function = new LanguageFunction(array(
					'function_id' => $id,
					'function_name' => $translation)
				);

			}
		}

		if (!$single_function || !isset($suggested_function)) {
			//Construct manually
			$suggested_function = new LanguageFunction(array(
				'function_name' => $translation)
			);
		}

		//First manipulate the code by substituting in the correct tags
/*		$search = array(CODE_TAG, END_CODE_TAG);
		$replace = array(CODE_TAG_REPLACE, END_CODE_TAG_REPLACE);*/
		$comment = strip_tags(Database::sanitize($comment), '<code><strong><i><ul><li><u>');
		if (!$note) {
			$type = ($suggested_function->function_id) ? SINGLE_TYPE : MULTIPLE_TYPE;
		}
		else {
			$type = NOTE_TYPE;
			$activity = new TranslationActivity(array(
				'linked_id' => $user_id,
				'identifier' => 1)
			);
		}

		$comment = preg_replace("/(\r|\n|(\<\s*br\s*\/?\s*>))+/i", "<br/>", $comment);

		$insert_translation_result = "INSERT INTO `translation_results` (translation_id, type, user_id, comment, date_posted) VALUES('$translation_id', '$type', '$user_id', '$comment', NOW())";
		$result = $mysqli->query($insert_translation_result)
		or die ($mysqli->error);
		$result_id = $mysqli->insert_id;


		if (!$note) {
			if ($suggested_function->function_id) {
				$insert = "INSERT INTO translation_singled (suggested_id, result_id) VALUES ('$suggested_function->function_id', '$result_id')";
				$result = $mysqli->query($insert)
				or die ($mysqli->error);
				$activity = new TranslationActivity(array(
					'linked_id' => $user_id,
					'identifier' => 0)
				);
			}
			else {
				$suggested_function->function_name = preg_replace("/(\r|\n|(\<\s*br\s*\/?\s*>))+/i", "<br/>", $suggested_function->function_name);
				$insert = "INSERT INTO translation_multiple (suggested_method, result_id) VALUES ('$suggested_function->function_name', '$result_id')";
				$result = $mysqli->query($insert)
				or die ($mysqli->error);
				$activity = new TranslationActivity(array(
					'linked_id' => $user_id,
					'identifier' => 0)
				);
			}
		}
		//Log the update
		$activity->translation_id = $translation_id;
		Translate::log_update($activity);
		http_response_code(200);
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException raised on request');
	}
}
catch(UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);
}