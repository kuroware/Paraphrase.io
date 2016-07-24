<?php
class ReputationChange {
	private $dbc;
	public $linked_id, $type, $trigger_user, $affected_user, $message, $change, $date, $reputation_id;

	public static $acceptable_change_reputation_reasons = array(
	1 => 'Translation upvoted',
	2 => 'Translation downvoted', 
	3 => 'Translation was un-upvoted', //Should not be logged,
	4 => 'Translation was un-downvoted', //Should not be logged,
	5 => 'Added a function/method',
	6 => 'Penalty for bogus function',
	7 => 'Post upvoted',
	8 => 'Post downvoted',
	9 => 'Post un-upvoted',
	10 => 'Post un-downvoted',
	11 => 'Question was upvoted',
	12 => 'Question downvoted',
	13 => 'Question un-upvoted',
	14 => 'Question un-downvoted',
	15 => 'Translation request was upvoted',
	16 => 'Translation requested was un-upvoted'
	);

	public static $defaults = array(
		'reputation_id' => null,
		'linked_id' => null,
		'type' => null,
		'trigger_user' => null,
		'affected_user' => null,
		'message' => null,
		'date' => null
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->reputation_id = (is_numeric($args['reputation_id'])) ? $args['reputation_id'] : null;	
		$this->linked_id = $args['linked_id'];
		$this->type = $args['type'];
		$this->trigger_user = $args['trigger_user'];
		$this->affected_user = $args['affected_user'];
		$this->message = $args['message'];
		$this->change = (is_numeric($args['change'])) ? $args['change'] : 0;
		$this->date = $args['date'];
	}
}