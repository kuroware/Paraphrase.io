<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
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
$request = file_get_contents('php://input');
$data = json_decode($request);
$result_id = $data->result_id;
$edit_type = $data->edit_type;
$edit_function_id = $data->edit_function_id;
$edit_summary = $data->edit_summary;
$edit_translation = $data->edit_translation;
/*$edit_type = 3;
$result_id = 18;
$edit_summary = 'This is a new edited note';*/

//Edit type is:
//	 1 = Note Type, just summary
//	 2 = Expects a linked function id
//	 3 = Translation + Summary
$user_id = User::get_current_user_id();
$mysqli = Database::connection();
try {
	if (is_numeric($result_id) && is_numeric($user_id)) {
		if (is_numeric($edit_type) && ($edit_type >= 1) && ($edit_type <= 3)) {
			switch ($edit_type) {
				case 1: 
					//A note type, edit the comment
					if ($edit_summary) {
						$edit_summary = trim(Database::sanitize($edit_summary));
						$edit_summary = preg_replace("/(\r|\n|(\<\s*br\s*\/?\s*>))+/i", "<br/>", $edit_summary);
						$sql = "UPDATE `translation_results` SET comment = '$edit_summary' WHERE result_id = '$result_id' LIMIT 1";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						http_response_code(200);
					}
					else {
						throw new UnexpectedValueException('UnexpectedValueException occured on request');
					}
					break;
				case 2:
					//A linked function id
					//Make sure the function_id exists in the database first
					if (is_numeric($edit_function_id)) {
						$function = new LanguageFunction(array(
							'function_id' => $edit_function_id));
						$exists = LanguageFunction::function_exists($function);
						if ($exists) {
							$sql = "UPDATE `translation_singled` SET suggested_id = '$edit_function_id' WHERE result_id = '$result_id' LIMIT 1";
							$result = $mysqli->query($sql)
							or die ($mysqli->error);
							http_response_code(200);
						}
						else {
							throw new BadMethodCallException('BadMethodCallException occured on request');
						}
					}
					else {
						throw new BadMethodCallException('BadMethodCallException occured on request');
					}
					break;
				case 3:
					//A translation and summary
					$edit_translation = trim(Database::sanitize($edit_translation));
					$edit_summary = trim(Database::sanitize($edit_summary));
					$edit_summary = preg_replace("/(\r|\n|(\<\s*br\s*\/?\s*>))+/i", "<br/>", $edit_summary);
					$edit_translation = preg_replace("/(\r|\n|(\<\s*br\s*\/?\s*>))+/i", "<br/>", $edit_translation);

					if ($edit_summary && $edit_translation) {
						$sql = "UPDATE `translation_results` SET comment = '$edit_summary' WHERE result_id = '$result_id' LIMIT 1";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);

						$sql = "UPDATE translation_multiple SET suggested_method = '$edit_translation' WHERE result_id = '$result_id' LIMIT 1";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						http_response_code(200);
					}
					else {
						throw new UnexpectedValueException('UnexpectedValueException occured on request');
					}
					break;
				default:
					throw new UnexpectedValueException('UnexpectedValueException occured on request');
			}
		}
	}
/*	if (is_numeric($result_id) && is_numeric($user_id)) {
		//Check to see if the current logged in user id is the author id
		$mysqli = Database::connection();
		$sql = "SELECT author_id FROM translation_results WHERE result_id = '$result_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		if ($result->num_rows == 1) {
			$author_id = mysqli_fetch_row($result)[0];
			if ($author_id == $user_id) {
				//Assume for now it is a custom translatoin
				list($editted_translation, $editted_comment) = Database::sanitize(array($editted_translation, $editted_comment));
				$sql = "UPDATE `translation_multiple` SET suggested_method = '$editted_translation' WHERE result_id = '$result_id'";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				$sql = "UPDATE `translation_results` SET comment = '$editted_comment' WHERE result_id = '$result_id'";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				http_response_code(200);
			}
			else {
				throw new OutOfBoundsException('OutOfBoundsException occured on request');
			}
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}*/
}
catch (UnexpectedValueException $e) {
	http_response_code(400);
	Database::print_exception($e);
}
catch (OutOfRangeException $e) {
	http_response_code(400);
	Database::print_exception($e);
}
catch (OutOfBoundsException $e) {
	http_response_code(400);
	Database::print_exception($e);
}
catch (BadMethodCallException $e) {
	http_response_code(400);
	Database::print_exception($e);
}