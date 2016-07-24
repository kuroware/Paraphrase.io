<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Edit')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Edit.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
$request = file_get_contents('php://input');
$data = json_decode($request);
$user_id = User::get_current_user_id();
$column = $data->column;
$val = Database::sanitize($data->val);
$function_id = $data->function_id;
/*$function_id = 5160;
$column = 3;
$val = 'sadsadasdh';*/
try {
	if (is_numeric($user_id) && is_numeric($function_id) && is_numeric($column)) {

		$user = new User(array(
			'user_id' => $user_id)
		);

		if ($user->points > EDIT_FUNCTION) {
		
				$mysqli = Database::connection();
				$editor = new User(array(
					'user_id' => $user_id)
				);
				$function = new LanguageFunction(array(
					'function_id' => $function_id)
				);
				$edit = new FunctionEdit(array(
					'column_id' => $column,
					'val' => $val,
					'function' => $function
					)
				);
			//	print_r($edit);
				$result = $editor->log_function_edit($edit);
				if ($result) {
					http_response_code(200);
				}
				else {
					throw new UnexpectedValueException('UnexpectedValueException occured on request');
				}
		}
		else {
			http_response_code(400); //Ummm we're spoofing this?
			throw new UnexpectedValueException('UnexpectedValueException occured on request, not enough reputation');
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
?>