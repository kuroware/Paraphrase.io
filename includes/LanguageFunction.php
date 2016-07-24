<?php
/*require_once __DIR__ . '/database.php';*/


class LanguageFunction {
	/*
	Class for some function in a language
	 */
	
	private $dbc;
	
	public $function_id, $function_name, $function_language, $big_o, $big_o_best_case, $big_o_worst_case;

	public static $defaults = array(
		'function_id' => null,
		'function_name' => null,
		'category_id' => null,
		'function_language' => null,
		'description' => null,
		'link' => null,
		'syntax' => null,
		'big_o_average' => null,
		'big_o_best_case' => null,
		'big_o_worst_case' => null,
		'big_o_average_summary' => null,
		'match_score' => 0, //For searching,
		'big_o_worst_case_notes' => null,
		'big_o_best_case_notes' => null
		);

	public static function function_exists(LanguageFunction $function) {
		/*
		(LanguageFunction) -> Bool
		Checks if a function object exists in the database
		 */
		if (is_numeric($function->function_id)) {
			$mysqli = Database::connection();
			$function_id = $function->function_id;
			$sql = "SELECT function_id FROM functions WHERE function_id = '$function_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			return ($result->num_rows == 1);
		}
		else {
			return false;
		}
	}
	public function __construct(array $args = array()) {
		/*
		Constructor for an object that is a function for some language
		 */
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);
		
		//Assign the object properites
		$this->dbc = Database::connection();
		$this->category_id = (is_numeric($args['category_id'])) ? $args['category_id'] : null;
		$this->function_id = (is_numeric($args['function_id'])) ? $args['function_id'] : null;
		$this->function_name = $args['function_name'];
		$this->function_language = (is_a($args['function_language'], 'Language')) ? $args['function_language'] : null;
		$this->description = $args['description'];
		$this->link = $args['link'];
		$this->syntax = $args['syntax'];
		$this->big_o_average_case = $args['big_o_average'];
		$this->big_o_best_case = $args['big_o_best_case'];
		$this->big_o_worst_case = $args['big_o_worst_case'];
		$this->big_o_average_summary = $args['big_o_average_summary'];
		$this->match_score = floatval($args['match_score']);
		$this->big_o_worst_case_notes = $args['big_o_worst_case_notes'];
		$this->big_o_best_case_notes = $args['big_o_best_case_notes'];
	}

	public function __autoload($class_name) {
		/*
		Last chance for PHP script to call a class name
		 */
		require_once __DIR__ . '/includes/' . $class_name . '.php';
	}

	public function collect_max_votes() {
		/*
		(Null) -> Null
		Attempts to collect the maxmium votes for the function and updates its category id
		 */
		$sql = "INSERT INTO `functions` (function_id, category_id)
					SELECT '$this->function_id' as function_id, category_id
					FROM (
						SELECT category_id, COUNT(function_id) AS c
						FROM `categorization_project`
						WHERE function_id = '$this->function_id'
						GROUP BY category_id
						ORDER BY c DESC LIMIT 1
					) as t
				ON DUPLICATE KEY UPDATE category_id = t.category_id
			";
		$result = $this->dbc->query($sql)
		or die ($this->dbc->error);
		return true;
	}
}
?>