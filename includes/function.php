<?php
require_once('database.php');


class LanguageFunction {
	/*
	Class for some function in a language
	 */
	
	private $dbc;
	
	public $function_id, $function_name, $function_language;

	public function __construct(array $args = array()) {
		/*
		Constructor for an object that is a function for some language
		 */
		
		//Assign the object properites
		$this->dbc = Database::connection();
		$this->function_id = (is_numeric($args['function_id'])) ? $args['function_id'] : null;
		$this->function_name = $args['function_name'];
		$this->function_language = (is_a($args['function_language'], 'Language')) ? $args['function_langauge'] : null;
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
}
?>