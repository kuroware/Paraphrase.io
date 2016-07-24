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
$result_id = $data->result_id;
//require_once '../../includes/Database.php';
try {
	if (is_numeric($result_id)) {
		$mysqli = Database::connection();
/*
		require_once '../../includes/Translate.php';
		require_once '../../includes/User.php';*/

		$user_id = User::get_current_user_id();

		$user = new User(array(
			'user_id' => $user_id)
		);

		$sql = "SELECT user_id, type FROM translation_results WHERE result_id = '$result_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		if ($result->num_rows == 1) {
			$row = mysqli_fetch_row($result);
			$single = ($row[1] == 1) ? true: false;
			$author = new User(array(
				'user_id' => $row[0])
			);
			$translation = new OutgoingTranslation(array(
				'translation_id' => $result_id,
				'single' => $single,
				'author' => $author)
			);
			$result = $user->delete_translation($translation);
			if ($result) {
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
	}
}
catch (UnexpectedValueException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (OutOfBoundsException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
catch (OutOfRangeException $e) {
	Database::print_exception($e);
	http_response_code(400);
}