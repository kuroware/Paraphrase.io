<?php
class FunctionNote {
	/*
	A note posted on some function to be noted of 
	 */
	private $dbc;
	public $note_id, $function, $note_text, $author, $date_posted, $upvotes, $downvote;

	public static $defaults = array(
		'note_id' => null,
		'function' => null,
		'note_text' => null,
		'author' => null,
		'upvotes' => 0,
		'downvotes' => 0,
		'date_posted' => null
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->dbc = Database::connection();
		$this->note_id = (is_numeric($args['note_id'])) ? $args['note_id'] : null;
		$this->note_text = $args['note_text'];
		$this->author = (is_a($args['author'], 'User')) ? $args['author'] : null;
		$this->upvotes = (is_numeric($args['upvotes'])) ? $args['upvotes'] : 0;
		$this->downvotes = (is_numeric($args['downvotes'])) ? $args['downvotes'] : 0;
		$this->status = $args['status'];
		if (is_null($args['status'])) {
			$this->upvoted = false;
			$this->downvoted = false;
		}
		else {
			if ($this->status == 1) {
				$this->upvoted = true;
				$this->downvoted = false;
			}
			else {
				$this->upvoted = false;
				$this->downvoted = true;
			}
		}
		$this->function = (is_a($args['function'], 'LanguageFunction')) ? $args['function'] : null;
		$this->date_posted = $args['date_posted'];
 	}

 	public function get_author_object() {
 		/*
 		(Null) - Null
 		Attempts to fetch the author object of the current function note object
 		 */
 		if ($this->note_id) {
 			$sql = "SELECT t1.author_id as `user_id`, t2.username
 			FROM function_notes as t1 
 			LEFT JOIN users as t2 
 			ON t2.user_id = t1.author_id
 			WHERE t1.note_id = '$this->note_id'";
 			$result = $this->dbc->query($sql)
 			or die ($this->dbc->error);
 			if ($result->num_rows == 1) {
 				$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
 			}
 			else {
 				$row = array();
 			}
 			$this->author = new User($row);
 		}
 	}

 	public function increment_upvotes() {
 		/*
 		(Null) -> Null
 		Increments the upvotes or the function note
 		 */
 		if ($this->note_id) {
 			$sql = "UPDATE `function_notes` SET upvotes = upvotes + 1 WHERE note_id = '$this->note_id' LIMIT 1";
 			$result = $this->dbc->query($sql)
 			or die ($thid->Dbc->error);
 			$this->upvotes++;
 			return true;
 		}
 		else {
 			return false;
 		}
 	}

 	public function increment_downvotes() {
 		/*
 		(Null) -> Null
 		Increments the downvotes for the function note
 		 */
 		if ($this->note_id) {
 			$sql = "UPDATE `function_notes` SET downvotes = downvotes + 1 WHERE note_id = '$this->note_id' LIMIT 1";
 			$result = $this->dbc->query($sql)
 			or die ($thid->Dbc->error);
 			$this->upvotes++;
 			return true;
 		}
 		else {
 			return false;
 		}	
 	}

 	public function decrement_upvotes() {
 		/*
 		(Null) -> Null
 		Drecrements the upvotes of the function note
 		 */
 		if ($this->note_id) {
 			$sql = "UPDATE `function_notes` SET upvotes = upvotes - 1 WHERE note_id = '$this->note_id' LIMIT 1";
 			$result = $this->dbc->query($sql)
 			or die ($thid->Dbc->error);
 			$this->upvotes++;
 			return true;
 		}
 		else {
 			return false;
 		}
 	}

 	public function decrement_downvotes() {
 		/*
 		(Nulll) -> Null
 		Decrements the downvotes for the function note
 		 */
  		if ($this->note_id) {
 			$sql = "UPDATE `function_notes` SET downvotes = downvotes - 1 WHERE note_id = '$this->note_id' LIMIT 1";
 			$result = $this->dbc->query($sql)
 			or die ($thid->Dbc->error);
 			$this->upvotes++;
 			return true;
 		}
 		else {
 			return false;
 		}
 	}
}

?>