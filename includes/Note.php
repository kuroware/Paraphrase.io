<?php
class Note {
	/*
	Class for a note that has been posted on some translation
	 */
	
	private $dbc;
	public $result_id, $translation_id, $note_text, $author;

	public static $defaults = array(
		'result_id' => null,
		'translation_id' => null,
		'author' => null,
		'note_text' => null
	);
	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->translation_id = $args['translation_id'];
		$this->result_id = $args['result_id'];
		$this->note_text = $args['comment'];
		$this->author = (is_a($args['author'], 'User')) ? $args['author'] : null;
	}
}