<?php
/*
PHP script that will tkae care of any edits directed to a function on the site
 */
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
$request = file_get_contents('php://input');
$data = json_decode($request);
$linked_id = $data->linked_id;
$type = $data->type;
$new_edit = $data->new_edit;
$user_id = User::get_current_user_id();
$reason = $data->reason;
/*$new_edit = '$key';
$reason = 'Mixed value that is a valid identifier for a key in an array that is to be checked against';
$linked_id = 16;
$type = 7;*/

try {
	if (is_numeric($linked_id) && is_numeric($type) && is_numeric($user_id) && ($new_edit)) {
		$mysqli = Database::connection(); //Mysqli connection variable

		//First confirm the function exists, if editting the function
		if ($type < 7) {
			$sql = "SELECT function_id FROM functions WHERE function_id = '$linked_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
		}
		if (($result->num_rows == 1) XOR ($type >= 7)) {
			//First sanitize input
			list($new_edit, $reason) = Database::sanitize(array($new_edit, $reason));

			//Run through possible edit types
			
			//First check if editting function parameter
			if (($type == 7) || ($type == 8)) {
				$sql = "UPDATE function_parameters SET parameter_description = '$reason', parameter_name = '$new_edit' WHERE parameter_id = '$linked_id'";
			}
			elseif (($type == 9) || ($type == 10)) {
				//Adding a parameter, rename the variables to something a bit more intuitive
				$parameter_name = $new_edit;
				$parameter_description = $reason;
				$param_type = ($type == 9) ? 0: 1;
				$sql = "INSERT INTO function_parameters (parameter_name, parameter_description, function_id, type) VALUES ('$parameter_name', '$parameter_description', '$linked_id', '$param_type')";
			}
			elseif ($type == 11) {
				$sql = "DELETE FROM function_parameters WHERE parameter_id = '$linked_id'";
			}
			else {
				//Something a bit more complicated, a big o edit
				switch ($type) {
					case 1:
						//Big O Worst Case
						$sql = "SELECT big_o_worst_case FROM functions WHERE function_id = '$linked_id'";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						$initial_chars = mysqli_fetch_row($result)[0];
						$initial_chars = (!$initial_chars) ? 0 : strlen($initial_chars);
						$sql = "UPDATE functions SET big_o_worst_case = '$new_edit' WHERE function_id = '$linked_id'";
						break;
					case 2:
						//Big O Best Case
						$sql = "SELECT big_o_best_case FROM functions WHERE function_id = '$linked_id'";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						$initial_chars = mysqli_fetch_row($result)[0];
						$initial_chars = (!$initial_chars) ? 0 : strlen($initial_chars);
						$sql = "UPDATE functions SET big_o_best_case = '$new_edit' WHERE function_id = '$linked_id'";
						break;
					case 3:
						//Big O Worst Case Notes
						$sql = "SELECT big_o_worst_case_notes FROM functions WHERE function_id = '$linked_id'";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						$initial_chars = mysqli_fetch_row($result)[0];
						$initial_chars = (!$initial_chars) ? 0 : strlen($initial_chars);
						$sql = "UPDATE functions SET big_o_worst_case_notes = '$new_edit' WHERE function_id = '$linked_id'";
						break;
					case 4:
						//Big O Best Case Notes
						$sql = "SELECT big_o_best_case_notes FROM functions WHERE function_id = '$linked_id'";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						$initial_chars = mysqli_fetch_row($result)[0];
						$initial_chars = (!$initial_chars) ? 0 : strlen($initial_chars);
						$sql = "UPDATE functions SET big_o_best_case_notes = '$new_edit' WHERE function_id = '$linked_id'";
						break;
					case 5:
						//Big O Average Case
						$sql = "SELECT big_o_average FROM functions WHERE function = '$linked_id'";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						$initial_chars = mysqli_fetch_row($result)[0];
						$initial_chars = (!$initial_chars) ? 0 : strlen($initial_chars);
						$sql = "UPDATE functions SET big_o_average = '$new_edit' WHERE function_id = '$linked_id'";
						break;
					case 6:
						//Big O Average Case Notes
						$sql = "SELECT big_o_average_summary FROM functions WHERE function = '$linked_id'";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);
						$initial_chars = mysqli_fetch_row($result)[0];
						$initial_chars = (!$initial_chars) ? 0 : strlen($initial_chars);
						$sql = "UPDATE functions SET big_o_average_summary = '$new_edit' WHERE function_id = '$linked_id'";
						break;
					default:
						throw new OutOfRangeException('OutOfRangeException occured on request');
				}
			}
			$result_edit = $mysqli->query($sql)
			or die ($mysqli->error);

			$changes = strlen($new_edit) - $initial_chars;

			//Log the edit, don't have edit developed yet so skip this for now
/*			$edit = new Edit(array(
				'reason' => $reason, 
				'linked_id' => $linked_id,
				'type' => $type,
				'changes' => $changes)
			);
			$result_log_edit = $user->log_edit($edit);
			if ($result_log_edit) {
				http_response_code(200);
			}
			else {
				throw new RuntimeException;
			}*/

			if (($type == 9) || ($type == 10)) {
				//Give back the new parameter to the request
				$parameter_id = $mysqli->insert_id;
				$parameter = new FunctionParameter(array(
					'parameter_id' => $parameter_id, 
					'parameter_name' => $new_edit,
					'parameter_description' => $reason,
					'type' => $param_type)
				);
				echo json_encode($parameter, JSON_PRETTY_PRINT);
				http_response_code(200);
			}
			http_response_code(200);
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
		}
	}
	else {	
		throw new UnexpectedValueException('UnexpectedValueException occured on request');
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (RuntimeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
?>
