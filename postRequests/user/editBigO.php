<?php
$request = file_get_contents('php://input');
$data = json_decode($request);
$user_id = User::get_current_user_id();
$key = $data->key;
$val = $data->val;
$linked_id = $data->linked_id;
try {
	if (is_numeric($user_id)) {
		$mysqli = Database::connection();
		$user = new user(array(
			'user_id' => $user_id)
		);
		$edit = new FunctionEdit(array(
			'key' => $key,
			'val' => $val,
			'linked_id' => $linked_id,
			'editor' => $user)
		);
		$user->log_function_edit($edit);
		//First attempt to log the edit
		$sql = "INSERT INTO function_edits (key, val, linked_id) VALUES('$edit->key', '$edit->val', '$edit->function->function_id', '$editor->user_id')";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
	}
}
?>