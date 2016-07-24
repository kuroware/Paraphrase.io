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
$function_id = $data->function_id;
$function_id = 89;
try {
	if(is_numeric($function_id)) {
		$sql = "SELECT t1.column, t1.val, t1.editor_id as `user_id`, t1.date_edited
		FROM `function_edits` as t1
		LEFT JOIN users as t2 
		ON t2.user_id = t1.editor_id
		WHERE t1.function_id = '$function_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$edits = array();
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$row['editor'] = new User($row);
			$edit = new FunctionEdit($row);
			$edits[] = $edit;
		}
		echo json_encode($edits, JSON_PRETTY_PRINT);
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