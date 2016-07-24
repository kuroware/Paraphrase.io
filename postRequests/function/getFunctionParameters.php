<?php
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
$function_id = $data->function_id;
/*$function_id = 27;*/
try {
	if (is_numeric($function_id)) {
		$mysqli = Database::connection();
		$sql = "SELECT parameter_id, parameter_description, parameter_name, type
		FROM function_parameters
		WHERE function_id = '$function_id'
		";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$return_array = array('parameters' => array(), 'returns' => array()); //Holding array for the parameters of the function
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$parameter = new FunctionParameter($row);
			if ($parameter->type == 'Input') {
				array_push($return_array['parameters'], $parameter);
			}
			else {
				array_push($return_array['returns'], $parameter);
			}
		}
		echo json_encode($return_array, JSON_PRETTY_PRINT);
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