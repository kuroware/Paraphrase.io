<?php
$request = file_get_contents('php://input');
$data = json_decode($request);
$function_id = $data->function_id;
$note_text = $data->note_text;
$user_id = User::get_current_user_id();
try {
	if (is_numeric($user_id) && ($note_text) && is_numeric($function_id) && is_numeric($user_id)) {
		//Check if the function exists first
		$function = new LanguageFunction(array(
			'function_id' => $function_id)
		);
		$exists = LanguageFunction::function_exists($function);
		if ($exists) {
			$sql = "INSERT INTO funciton_notes (function_id, note_text, author_id) VALUES ('$function->function_id', '$note->note_text', '$user->user_id')";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);

		}
		else{
			throw new OutOfRangeException('OutOfRangeException occured on reuqest');
		}
	}
}