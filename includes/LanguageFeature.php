<?php
class LanguageFeature {
	private $dbc;

	public $feature_id, $feature_name, $summary;

	public static $defaults = array(
		'feature_id' => null,
		'feature_name' => null,
		'summary' => null,
		'status' => null
		);

	public function __construct($args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Assign the object properites
		$this->feature_id = (is_numeric($args['feature_id'])) ?$args['feature_id'] : null;
		$this->feature_name = $args['feature_name'];
		$this->status = $args['status'];
		$this->summary = $args['summary'];
	}

	public function __autoload($class_name) {
		/*
		Last chance for PHP script to call a class name
		 */
		require_once __DIR__ . '/includes/' . $class_name . '.php';
	}
}