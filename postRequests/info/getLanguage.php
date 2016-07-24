<?php
header("Content-Type: application/json"); //Set header for outputing the JSON information
$request = file_get_contents('php://input');
$data = json_decode($request);
$language_id = $data->language_id;
/*$language_id = 1;*/
require_once '../../includes/Database.php';
require_once '../../includes/Language.php';
try{
	if (is_numeric($language_id)) {
		$mysqli = Database::connection();

		$mysqli->set_charset('utf8');

		$sql = "SELECT language_name, summary, icon, version FROM languages WHERE language_id = '$language_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);

		if ($result->num_rows == 1) {
			require_once '../../includes/LanguageFeature.php';
			$row = mysqli_fetch_array($result);

			$sql = "SELECT t1.feature_id, t1.feature_name, COALESCE(t2.summary, 'N/A') as summary, COALESCE(t2.status, '-') as status
			FROM language_features as t1
			LEFT JOIN language_features_info as t2
			ON t2.feature_id = t1.feature_id
			AND t2.language_id = '$language_id'
			ORDER BY t1.feature_name ASC";

			$result = $mysqli->query($sql)
			or die ($mysqli->error);

			$row['features'] = array();
			while ($row_x = mysqli_fetch_array($result)) {
				$feature = new LanguageFeature($row_x);
				$row['features'][] = $feature;
			}

			$language = new Language($row);
			$language->summary = nl2br($language->summary);
			echo json_encode($language, JSON_PRETTY_PRINT);
			http_response_code(200);
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
		}
	}
	else {
		throw  new UnexpectedValueException('UnexpectedValueException occured on request');
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