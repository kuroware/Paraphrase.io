<?php
/*require_once __DIR__ . '/database.php';*/


abstract class Comment {
	/*
	Abstract class for a comment
	 */
}


class TranslationComment extends Comment {
	/*
	Class for a comment on a translation, may have a suggested function
	 */
	
	private $dbc;	
	public $comment_id, $translation_id, $comment_text, $date_posted;

	public static $defaults = array(
		'comment_id' => null,
		'comment_text' => null,
		'linked_translation' => null, 
		'date_posted' => null,
		'author' => null
		);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Construct the object properties
		$this->dbc = Database::connection();
		$this->comment_id = (is_numeric($args['comment_id'])) ? $args['comment_id'] : null;
		$this->comment_text = $args['comment_text'];
		$this->linked_translation = (is_a($args['linked_translation'], 'Translate')) ? $args['linked_translation'] : null;
		$this->date_posted = $args['date_posted'];
		$this->author = (is_a($args['author'], 'User')) ? $args['author'] : null;
	}

	public function increment_upvotes() {
		/*
		(Null) -> Bool
		Attempts to increment the upvotes for a comment
		 */
		if ($this->comment_id) {
			$sql = "UPDATE translation_comments SET upvotes = upvotes + 1 WHERE comment_id = '$this->comment_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
		}
	}
}


class QuestionComment extends Comment {
	private $dbc;
	public $comment_id, $comment_text, $author, $date_posted;

	public static $defaults = array(
		'comment_id' => null,
		'comment_text' => null,
		'author' => null,
		'date_posted' => null
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Construct the object properities
		$this->comment_id = (is_numeric($args['comment_id'])) ? $args['comment_id'] : null;
		$this->comment_text = $args['comment_text'];
		$this->author = (is_a($args['author'], 'User')) ? $args['author'] : null;
		$this->date_posted = $args['date_posted'];
	}
}


class ProfileComment extends Comment {
	private $dbc;
	public $comment_id, $comment_text, $author, $date_posted, $profile;

	public static $defaults = array(
		'comment_id' => 0,
		'comment_text' => null,
		'author' => null,
		'date_posted' => null,
		'profile' => null
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->dbc = Database::connection();
		$this->comment_id = (is_numeric($args['comment_id'])) ? $args['comment_id'] : null;
		$this->comment_text = $args['comment_text'];
		$this->date_posted = $args['date_posted'];
		$this->author = (is_a($args['author'], 'User')) ? $args['author'] : null;
		$this->profile = (is_a($args['profile'], 'User')) ? $args['profile'] : null;
	}
}