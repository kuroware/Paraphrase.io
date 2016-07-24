<?php
class FunctionParameter {
	/*
	Basic class for a function parameter for some function
	 */
	private $dbc; //For any connection variables that may/may not be made

	public $parameter_id, $parameter_name, $parameter_description, $type;

	public static $defaults = array(
		'parameter_id' => null,
		'parameter_name' => null,
		'parameter_description' => null,
		'type' => null
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Assign the object properites
		$this->parameter_id = $args['parameter_id'];
		$this->parameter_name = $args['parameter_name'];
		$this->parameter_description = $args['parameter_description'];
		if ($args['type'] == 1 || $args['type'] == 0) {
			$this->type = ($args['type'] == 0) ? 'Input' : 'Output';
		}
		else {
			$this->type = null;
		}
	}

	public function __autoload($class_name) {
		/*
		Last chance for PHP script to call a class name
		 */
		require_once __DIR__ . '/includes/' . $class_name . '.php';
	}
}