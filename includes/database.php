<?php
require_once __DIR__ . '/exceptions.php';
require_once __DIR__ . '/../vars/constants.php';


class Database {
	/*
	Class for the database
	 */

	private static $connection;

	public function __construct() {
		$this->dbc = self::connection();
	}

	public function log_feedback() {
		/*
		(Null) -> Int
		Logs the current ip address as a sender and returns number of rows inserted based on rate limit
		 */
		$ip = $_SERVER['REMOTE_ADDR'];
		$sql_exists = "
			SELECT ip_address FROM `feedback_record`
			WHERE ip_address = '$ip'
			AND NOW() <= DATE_ADD(date_sent, INTERVAL 5 SECOND)
		";
	//	echo $sql_exists;
		$result = $this->dbc->query($sql_exists)
		or die ($this->dbc->error);

		if ($result->num_rows == 0) {
			$sql = "INSERT INTO `feedback_record` (ip_address, date_sent) VALUES('$ip', NOW())";
			//echo $sql;
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return 1;
		}
		else {
			return 0;
		}
	}

	public static function connection() {
		/*
		Provides a connection variable for usage
		 */
		require_once __DIR__ . '/../vars/connectvars.php';
		if (!self::$connection) {
			$mysqli = new mysqli(HOST, USER, PASS, DATABASE);
			self::$connection = $mysqli;
			return self::$connection;
		}
		else {
			return self::$connection;
		}
	}

	public static function secondsToTime($seconds) {
	    $dtF = new DateTime("@0");
	    $dtT = new DateTime("@$seconds");
	    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
	}

	public static function clear_temp_views() {
		$mysqli = self::connection();
		$sql = "DELETE FROM temp_views WHERE date <= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		return true;
	}

	public static function pull_search_stats_today() {
		/*
		(Null) -> Array('translations_done_today' => null, 'new_translation_requests_today' => null)
		Pulls the search stats for today
		 */
		$mysqli = self::connection();
		$sql = "SELECT value FROM integral WHERE type = '" . TRANSLATIONS_DONE_TYPE. "' AND date = CURDATE() LIMIT 1";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if ($result->num_rows == 0) {
			$translations_today = 0;
		}
		else {
			$translations_today = mysqli_fetch_row($result)[0];
		}
		return array('translations_done_today' => $translations_today, 'new_translation_requests_today' => 0);
	}

	public static function log_translation() {
		/*
		(Null) -> Bool
		Attempts to log the translation done today and increment it into the database
		 */
		$mysqli = self::connection();
		$sql = "INSERT INTO integral (language_id, value, date, type) VALUES (0, 1, CURDATE(), " . TRANSLATIONS_DONE_TYPE. ")
		ON DUPLICATE KEY UPDATE value = value + 1";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		return true;
	}

	protected function __autoload($name) {
		/*
		PHP Magic Method for autoloading
		 */
		try {
			$result =require_once($name . '/php');
			if (!$result) {
				throw new UnloadableClass('Could not load class name ' . $name);
			}
		}
		catch (UnloadableClass $e) {
			self::print_exception($e);
		}
	}

	public static function print_exception(Exception $e) {
		echo json_encode(array(
			'message' => $e->getMessage(),
			'line' => $e->getLine(),
			'file' => $e->getFile()),
		JSON_PRETTY_PRINT
		);
	}

	public static function sanitize($input) {
		/*
		(Mixed) -> Mixed
		 */
		$mysqli = self::connection();
		if (is_array($input)) {
			foreach ($input as $key=>$val) {
				$input[$key] = mysqli_real_escape_string($mysqli, trim($val));
			}
		}
		else {
			$input = mysqli_real_escape_string($mysqli, trim($input));
		}
		return $input;
	}
}