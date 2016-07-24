<?php
session_start();
header("Content-Type: application/json"); //Set header for outputing the JSON information
//PHP script for parsing a new registration request for a user
$request = file_get_contents('php://input');
$data = json_decode($request);

//Autoloading script
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
try {
	$username = $data->username;
	$password = $data->password;
	$email = $data->email;
/*	$username = 'Derek';
	$password = 'shufflr';
	$email = 'itismostcool@gmail.com';*/
	if ($username && $password && $email && filter_var($email, FILTER_VALIDATE_EMAIL) && (strlen($password) >= 5) && (strlen($username) >= 5)) {
		if (!is_numeric(User::get_current_user_id())) {
			//The inputs are valid
			$mysqli = Database::connection();
			$username = Database::sanitize($username);
			$password = Database::sanitize(password_hash($password, PASSWORD_BCRYPT));
			$email = Database::sanitize($email);

			$new_user = new User(array(
				'username' => $username)
			);

			if (!User::user_exists($new_user)) {
				//Create token
				$size = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
				$new_token = bin2hex(mcrypt_create_iv($size, MCRYPT_DEV_RANDOM));
				$new_token_hashed = password_hash($new_token, PASSWORD_BCRYPT);

				//SQL insertion statement
				$sql = "INSERT INTO users (username, password, email, token, last_logged_in, date_joined) VALUES ('$username', '$password', '$email', '$new_token_hashed', NOW(), CURDATE())";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);

				$id = $mysqli->insert_id;

				//Set the cookies and sessions
				setcookie('token', $new_token, time() + 3600, "/");
				setcookie('user_id', $id, time() + 3600, "/");
				$_COOKIE['user_id'] = $id;
				$_COOKIE['token'] = $new_token;
				$_SESSION['user_id'] = $id;

				$new_user->user_id = $id;

				echo json_encode($new_user);
				http_response_code(200);
			}
			else {
				throw new OutOfRangeException('User already exists');
			}
		}
		else {
			throw new OutOfBoundsException('User already logged in');
		}
	}
	else {
		throw new UnexpectedValueException('UnexpectedValueException occured');
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
catch (OutOfBoundsException $e) {
	Database::print_exception($e);
	http_response_code(400);
}
?>