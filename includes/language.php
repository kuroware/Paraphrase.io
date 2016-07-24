<?php

/*require_once __DIR__ . '/database.php';*/
require_once __DIR__ . '/../vars/constants.php';


class Language {
	/*
	Class for some language
	 */
	
	private $dbc;
	
	public $language_id, $language_name, $summary, $icon;

	public static $defaults = array(
		'language_id' => null,
		'language_name' => null,
		'summary' => null,
		'icon' => null,
		'version' => null,
		'features' => array()
	);

	public function __construct(array $args = array()) {
		/*
		Constructor for a language
		 */
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Assign the object properites
		$this->dbc = Database::connection();
		$this->language_id = (is_numeric($args['language_id'])) ? $args['language_id'] : null;
		$this->language_name = $args['language_name'];
		$this->summary = $args['summary'];
		$this->icon = ICON_DIR . $args['icon'];
		$this->version = $args['version'];
		$this->features = (is_array($args['features'])) ? $args['features'] : array();
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