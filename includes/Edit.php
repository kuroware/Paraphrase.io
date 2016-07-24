<?php
class Edit {
	/*
	Class for some edit that has occured on some content
	 */
	private $dbc;
	public $edit_id, $linked_id, $type, $changes, $reason, $edit_text;

	public static $edit_contracts = array(
		1 => 'Big O Worst Case',
		2 => 'Big O Best Case',
		3 => 'Big O Worst Case Notes',
		4 => 'Big O Best Case Notes', 
		5 => 'Big O Average Case', 
		6 => 'Big O Average Case Note',
		7 => 'Parameter',
		8 => 'Return',
		9 => 'Adding a new parameter'
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->dbc = Database::connection();
		$this->edit_id = (is_numeric($args['edit_id'])) ? $args['edit_id'] : null;
		$this->edit_text = $args['edit_text'];
		$this->linked_id = (is_numeric($args['linked_id'])) ? $args['linked_id'] : null;
		$this->changes = (is_numeric($args['changes'])) ? $args['changes'] : 0; //This object property signifies the character changes for the edit
		$this->date_edited = $args['date_edited'];
		$this->editor = (is_a($args['editor'], 'User')) ? $args['editor'] : null;
		$this->reason = $args['reason'];
	}
}

class FunctionEdit {

	private $dbc;
	public $column, $val, $function_id;

	public static $columns = array(
		1 => 'function_name',
		2 => 'big_o_average',
		3 => 'big_o_average_summary	',
		4 => 'big_o_worst_case',
		5 => 'big_o_worst_case_notes',
		6 => 'big_o_best_case',
		7 => 'big_o_best_case_notes'
	);

	public static $column_names = array(
		1 => 'Function name', 
		2 => 'Big O Average',
		3 => 'Big O Average Notes',
		4 => 'Big O Worst Case',
		5 => 'Big O Worst Case Notes',
		6 => 'Big O Best Case', 
		7 => 'Big O Best Case Note'
	);

	private static $defaults = array(
		'edit_id' => null,
		'column_id' => null,
		'val' => null,
		'function' => null,
		'editor' => null,
		'date_edited' => null,
		'column_name' => null
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->edit_id = $args['edit_id'];
		$this->column = (array_key_exists($args['column_id'], self::$columns)) ? $args['column_id'] : null;
		$this->val = $args['val'];
		$this->function = (is_a($args['function'], 'LanguageFunction')) ? $args['function'] : null;
		$this->editor = (is_a($args['editor'], 'User')) ? $args['editor'] : null;
		$this->date_edited = $args['date_edited'];
		$this->column_name = self::$column_names[$args['column_id']];
	}
}