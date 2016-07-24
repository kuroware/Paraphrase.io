<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
/*require_once __DIR__ . '/Database.php';*/
require_once __DIR__ . '/../vars/constants.php';


class User {
	private $dbc;
	
	public $user_id, $username, $points, $avatar, $description;

	private $admins = array(2);

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
	16 => 'Translation requested was un-upvoted',
	17 => 'Function note upvoted',
	18 => 'Function note un-upvoted',
	19 => 'Function note downvoted',
	20 => 'Function note un-downvoted'
	);

	public static $unlogged_change_reputation_reasons = array(
		3, 4, 9, 10
	);

	public static $defaults = array(
		'user_id' => null,
		'username' => null,
		'avatar' => null,
		'description' => null,
		'points' => 0,
		'upvotes' => 0,
		'downvotes' => 0,
		'ud_ratio' => '-',
		'answers' => 0,
		'requests' => 0,
		'date_joined' => null,
		'views' => 0,
		'email' => null,
		'location' => null,
		'github' => null,
		'se' => null,
		'last_logged_in_ago' => null,
		'weekly_ranking_change' => null,
		'daily_ranking_change' => null,
		'yearly_ranking_change' => null,
		'previous_week_ranking' => null,
		'previous_day_ranking' => null,
		'comments' => array()
		);

	public function __construct(array $args = array()) {
		$this->dbc = Database::connection();
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Assign the object properites
		$this->user_id = (is_numeric($args['user_id'])) ? $args['user_id'] : null;
		$this->username = $args['username'];
		$this->avatar = AVATAR_DIR . $args['avatar'];
		$this->description = $args['description'];
		$this->points = (is_numeric($args['points'])) ? number_format($args['points']) : 0;
		$this->upvotes = (is_numeric($args['upvotes'])) ? number_format($args['upvotes']) : 0;
		$this->downvotes = (is_numeric($args['downvotes'])) ? number_format($args['downvotes']) : 0;
		$this->ud_ratio = (is_numeric($args['ud_ratio'])) ? $args['ud_ratio'] : '-';
		$this->answers = (is_numeric($args['answers'])) ? number_format($args['answers']) : 0;
		$this->requests = (is_numeric($args['requests'])) ? number_format($args['requests']) : 0;
		$this->date_joined = $args['date_joined'];
		$this->views = (is_numeric($args['views'])) ? $args['views'] : 0;
		$this->email = $args['email'];
		$this->location = $args['location'];
		$this->stack_exchange = $args['se'];
		$this->github = $args['github'];
		$this->last_logged_in = ($args['last_logged_in_ago']) ? Database::secondsToTime($args['last_logged_in_ago']) : null;
		$this->weekly_ranking_change = (is_numeric($args['weekly_ranking_change'])) ? $args['weekly_ranking_change'] : 0;
		$this->yearly_ranking_change = (is_numeric($args['yearly_ranking_change'])) ? $args['yearly_ranking_change'] : 0;
		$this->daily_ranking_change = (is_numeric($args['daily_ranking_change'])) ? $args['daily_ranking_change'] : 0;
		$this->previous_week_ranking = (is_numeric($args['previous_week_ranking'])) ? $args['previous_week_ranking'] : 0;
		$this->previous_day_ranking = (is_numeric($args['previous_day_ranking'])) ? $args['previous_day_ranking'] : 0;
		$this->comments = (is_array($args['comments'])) ? $args['comments'] : array();

		$formatted_string_date = ''; //The temporary variable to hold the formatted string date
		if ($this->last_logged_in) {
			//echo $this->action_date;
			//Attempt to style the action date
			$x = explode(', ', $this->last_logged_in);
			//print_r($x);
			foreach ($x as $key=>$val) {
				//echo $val;
				//Now go through and select the most relevant one, only adding the ones without 0 in it
				if (!strpos($val, 'minutes')) {
					//There are on minutes in this string so it can only be hours or days
					if (substr($val, 0, 1) != '0') {
						//echo 'found';
						$formatted_string_date .= $val;
						break; //The loop is done, grabbed the most relevant info
					}
				}
				else {
					//echo 'found';
					//Else this loop is minutes, which means the time can onnly be x minutes or x seconds
					$possible_time = explode('and ', $val);
					//print_r($possible_time);
					if (substr($val, 0, 1) == 0) {
						//Minutes is 0, only add seconds then
						$formatted_string_date .= $possible_time[1];
					}
					else {
						//Minutes is not 0, add the minutes
						$formatted_string_date .= $possible_time[0];
					}
				}
			}
			//Trim the string
			$formatted_string_date = trim($formatted_string_date);
			$this->last_logged_in = $formatted_string_date;
		}

		//A bit of patching up
/*		if ($this->last_logged_in) {
			$x = explode(', ', $this->last_logged_in);
			$this->last_logged_in = '';
			foreach ($x as $key=>$val) {
				if (substr($val, 0, 1) != '0') {
					$this->last_logged_in .= $val;
				}
			}
		}*/
	}

	public function is_admin() {
		/*
		Checks via the currently logged in user and not the set property
		 */
		$user_id = self::get_current_user_id();
		if (in_array($user_id, $this->admins)) {
			return true;
		}
		else {
			return false;
		}
	}

	public static function get_current_user() {
		/*
		(Null) -> User
		Gets the current user object
		 */
		$mysqli = Database::connection();
		$user_id = self::get_current_user_id();
		if ($user_id == 'None') {
			//Anonymous user
			$user_id = 3;
			$username = 'Anonymous';
		}
		else {
			$sql_fetch_username = "SELECT username FROM users WHERE user_id = '$user_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$username = mysqli_fetch_row($result)[0];
		}
		$user_object = new User(array(
			'user_id' => $user_id, 
			'username' => $username)
		);
		return $user_object;
	}
	
	public function post_translation(OutgoingTranlsation $translation) {
		/*
		(OutgoingTranslation) -> Bool
		Posts a user generated translation into the database
		 */
		
	}

	public function repot_fake_function(LanguageFunction $function) {
		/*
		(LanguageFunciton) -> Bool
		Reports the function as fake and modifies reputation as needed
		 */
	/*	if ($this->user_id) {
			$sql = "INSERT INTO "
		}*/
	}

	public static function user_exists($user_parameter) {
		/*
		Mixed -> Bool
		Based on a mixed parameter, attempts to check if the user exists
		 */
		$mysqli = Database::connection(); //MySQL connection variable
		if (is_a($user_parameter, 'User')) {
			if ($user->user_id) {
				//The user id is valid and attempt to check
				$sql = "SELECT user_id FROM users WHERE user_id = '$user->user_id'";
			}
			else {
				$sql = "SELECT user_id FROM users WHERE username = '$user->username'";
			}
		}
		elseif (is_numeric($user_parameter)) {
			$sql = "SELECT user_id FROM users WHERE user_id = '$user_parameter'";
		}
		elseif ($user_parameter) {
			$sql = "SELECT user_id FROM users WHERE username = '$user_parameter'";
		}
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		return ($result->num_rows == 1);
	}

	public function get_fields() {
		/*
		(Null) -> Null
		Populates the basic fields of the user based on the user if
		 */
		if ($this->user_id) {
			$sql = "SELECT username, avatar, points FROM users WHERE user_id = '$this->user_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			if ($result->num_rows == 1) {
				$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
				$this->username = $row['username'];
				$this->points = $row['points'];
				$this->avatar = AVATAR_DIR . $row['avatar'];
			}
		}
	}

	public function unupvote_upvote_post(Post $post) {
		/*
		(Post) -> Bool
		Attempts to upvote the post
		 */
		if ($this->user_id && $post->post_id) {
			$sql = "INSERT INTO posts_feedback (post_id, user_id, status) VALUES ('$post->post_id', '$this->user_id', 1)
			ON DUPLICATE KEY UPDATE status = 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected_rows = $this->dbc->affected_rows;
			switch ($affected_row) {
				case 0:
					//Attempting to unupvote
					$sql = "DELETE FROM posts_feedback WHERE user_id ='$this->user_id' AND post_id = '$post->post_id'";
					$result = $mysqli->query($sql)
					or die ($mysqli->error);
					$post->decrement_upvotes();
					$author_id = Post::get_author_id_by_post_id($post->post_id);
					$author = new Author(array(
						'user_id' => $author_id )
					);
					$author->change_reputation(-15, 9, $this->user_id);
					break;
				case 1:
					$post->increment_upvotes();
					$author->change_reputation(15, 7, $this->user_id);
					break;
				case 2:
					$post->decrement_downvotes();
					$post->increment_upvotes();
					$author->change_reputation(5, 10, $this->user_id);
					$author->change_reputation(15, 7, $this->user_id);
					break;
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function undownvote_downvote_question(Question $question) {
		/*
		(Post) -> Bool
		Attempts to downvote the question
		 */
		if ($this->user_id && $question->question_id) {
			$sql = "INSERT INTO question_feedback (question_id, user_id, status) VALUES ('$question->question_id', '$this->user_id', 2)
			ON DUPLICATE KEY UPDATE status = 2";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected_rows = $this->dbc->affected_rows;
			switch ($affected_rows) {
				case 0:
					//Attempting to undownvote
					$sql = "DELETE FROM question_feedback WHERE user_id ='$this->user_id' AND question_id = '$question->question_id'";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$question->decrement_downvotes();
					$author = Question::get_author_by_question($question);
					$author->change_reputation(10, 14, $this->user_id);
					break;
				case 1:
					$question->increment_downvotes();
					$author = Question::get_author_by_question($question);
					$author->change_reputation(-10, 12, $this->user_id);
					break;
				case 2:
					$question->decrement_upvotes();
					$question->increment_downvotes();
					$author = Question::get_author_by_question($question);
					$author->change_reputation(-20, 13, $this->user_id);
					$author->change_reputation(-10, 12, $this->user_id);
					break;
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function unupvote_upvote_question(Question $question) {
		/*
		(Post) -> Bool
		Attempts to downvote the question
		 */
		if ($this->user_id && $question->question_id) {
			$sql = "INSERT INTO question_feedback (question_id, user_id, status) VALUES ('$question->question_id', '$this->user_id', 1)
			ON DUPLICATE KEY UPDATE status = 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected_rows = $this->dbc->affected_rows;
			switch ($affected_rows) {
				case 0:
					//Attempting to unupvote
					$sql = "DELETE FROM question_feedback WHERE user_id ='$this->user_id' AND question_id = '$question->question_id'";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$question->decrement_upvotes();
					$author = Question::get_author_by_question($question);
					$author->change_reputation(-20, 13, $this->user_id);
					break;
				case 1:
					$question->increment_upvotes();
					$author = Question::get_author_by_question($question);
					$author->change_reputation(20, 11, $this->user_id);
					break;
				case 2:
					$question->decrement_downvotes();
					$question->increment_upvotes();
					$author = Question::get_author_by_question($question);
					$author->change_reputation(10, 14, $this->user_id);
					$author->change_reputation(20, 11, $this->user_id);
					break;
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function undownvote_downvote_post(Post $post) {
		/*
		(Post) -> Bool
		Attempts to upvote the post
		 */
		if ($this->user_id && $post->post_id) {
			$sql = "INSERT INTO posts_feedback (post_id, user_id, status) VALUES ('$post->post_id', '$this->user_id', 1)
			ON DUPLICATE KEY UPDATE status = 2";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected_rows = $this->dbc->affected_rows;
			switch ($affected_row) {
				case 0:
					//Attempting to unupvote
					$sql = "DELETE FROM posts_feedback WHERE user_id ='$this->user_id' AND post_id = '$post->post_id'";
					$result = $mysqli->query($sql)
					or die ($mysqli->error);
					$post->decrement_downvotes();
					$author_id = Post::get_author_id_by_post_id($post->post_id);
					$author = new Author(array(
						'user_id' => $author_id )
					);
					$author->change_reputation(15, 10, $this->user_id);
					break;
				case 1:
					$post->increment_downvotes();
					$author->change_reputation(15, 7, $this->user_id);
					break;
				case 2:
					$post->decrement_upvotes();
					$post->increment_downvotes();
					$author->change_reputation(5, 9, $this->user_id);
					$author->change_reputation(15, 8, $this->user_id);
					break;
			}
			return true;
		}
		else {
			return false;
		}
	}

	final public function change_reputation($change, $reason, $linked_id) {
		/*
		(int, int, int) -> Bool
		user_id is the user who has triggered the action, the actual method call should be self-refernecing
		Attempts to modify and log a change in a user's reputation
		 */
		if (array_key_exists($reason, self::$acceptable_change_reputation_reasons) && $this->user_id && is_numeric($reason) && is_numeric($change) && is_numeric($linked_id)) {
			//Reason is valid, attempt the change
			/*$reason = self::$acceptable_change_reputation_reasons[$reason];*/
			$sql = "INSERT INTO reputation_changes (increment_by, type, user_id, linked_id, date) VALUES ('$change', '$reason', '$this->user_id', '$linked_id', NOW())";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);

			//Update the user profile, for now ensure that user points cannot fall below 0
			$sql = "UPDATE users SET points = IF(points + '$change' > 0, points + '$change', 0) WHERE user_id = '$this->user_id'";
/*			$sql = "UPDATE users SET points = points ";
			$sql .= ($change < 0) ? "- " . abs($change) : " + '$change'";
			$sql .= " WHERE user_id = '$this->user_id'";*/
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			//echo 'fals';
			return false;
		}
	}

	public function delete_function_note(FunctionNote $function_note) {
		/*
		(FunctionNote) -> Bool
		Attempts to delete the given function note
		 */
		if ($function_note->note_id) {
			if (!$function_note->author){
				$function_note->get_author_object();
			}
			if ($function_note->author->user_id == $this->user_id) {
				//The author is the current user, delete
				$sql = "DELETE FROM `function_notes` WHERE note_id = '$function_note->note_id'";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function post_function_note(FunctionNote $function_note) {
		/*
		(FunctionNote) -> Mixed (FunctionNote/Bool) 
		Attempts to post a function note under the current user, assumed input is already sanitized
		 */
		if ($this->user_id && !$function_note->note_id) {
			$sql = "INSERT INTO `function_notes` (note_text, author_id, function_id, date_posted) VALUES('$function_note->note_text', '$this->user_id', '" . $function_note->function->function_id. "', NOW())";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$id = $this->dbc->insert_id;
			$function_note->note_id = $id;
			$function_note->author = clone $this;
			$function_note->date_posted = date('M j, y');
			return $function_note;
		}
		else {
			return false;
		}
	}

	public function edit_function_note(FunctionNote $function_note) {
		/*
		(FunctionNote) -> Bool
		Attempts to edit the current function note with the supplied parameters, assumes that data has already been sanitized
		 */
		if ($this->user_id && $function_note->note_id) {
			$function_note->get_author_object(); //To ensure that the author information is up to date
			if ($function_note->author->user_id == $this->user_id) 
			$sql = "UPDATE `function_notes` SET note_text = '$function_note->note_text' WHERE note_id = '$function_note->note_id' LIMIT 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public function undownvote_downvote_function_note(FunctionNote $function_note) {
		/*
		(FunctionNote) -> Bool
		Downvotes a function note and deals with the request accordingly
		 */

		if ($function_note->note_id) {
			$sql = "INSERT INTO function_note_feedback (user_id, note_id, status) VALUES('$this->user_id', '$function_note->note_id', 0)
			ON DUPLICATE KEY UPDATE status = 0";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected_rows = $this->dbc->affected_rows;
			switch ($affected_rows) {
				case 0:
					//The same thing was inserted, try to un-downvote
					$sql = "DELETE FROM function_note_feedback WHERE user_id = '$this->user_id' AND note_id = '$function_note->note_id'";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$function_note->decrement_downvotes();
					$function_note->get_author_object();
					$author = $function_note->author;
					$author->change_reputation(10, 20, $function_note->note_id);
					break;
				case 1:
					//New row inseted, tried todownvote
					$function_note->increment_downvotes();
					$function_note->get_author_object();
					$author = $function_note->author;
					$author->change_reputation(-10, 19, $function_note->note_id);
					break;
				case 2:
					//Downvoted from an upvote
					$function_note->decrement_upvotes();
					$function_note->increment_downvotes();

					//Change the reputation of the author
					$function_note->get_author_object();
					$author = $function_note->author;
					$author->change_reputation(-10, 19, $function_note->note_id);
					$author->change_reputation(-10, 18, $function_note->note_id);
					break;
			}
			return true;
		}
		else {
			return false;
		}
		
	}

	public function unupvote_upvote_function_note(FunctionNote $function_note) {
		/*
		(FunctionNote) -> Bool
		Upvotes a function note and deals with the request accordingly
		 */
		if ($function_note->note_id) {
			$sql = "INSERT INTO function_note_feedback (user_id, note_id, status) VALUES('$this->user_id', '$function_note->note_id', 1)
			ON DUPLICATE KEY UPDATE status = 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected_rows = $this->dbc->affected_rows;
			switch ($affected_rows) {
				case 0:
					//The same thing was inserted, try to un-upvote
					$sql = "DELETE FROM function_note_feedback WHERE user_id = '$this->user_id' AND note_id = '$function_note->note_id'";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$function_note->decrement_upvotes();
					$function_note->get_author_object();
					$author = $function_note->author;
					$author->change_reputation(-10, 19, $function_note->note_id);
					break;
				case 1:
					//New row inseted, tried to upvote
					$function_note->increment_upvotes();
					$function_note->get_author_object();
					$author = $function_note->author;
					$author->change_reputation(10, 17, $function_note->note_id);
					break;
				case 2:
					//Upvoted from a downvote
					$function_note->decrement_downvotes();
					$function_note->increment_upvotes();

					//Change the reputation of the author
					$function_note->get_author_object();
					$author = $function_note->author;
					$author->change_reputation(10, 20, $function_note->note_id);
					$author->change_reputation(10, 17, $function_note->note_id);
					break;
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function log_function_edit(FunctionEdit $edit) {
		/*
		(FunctionEdit) -> Bool
		Logs a function edit under the current user
		 */
		if ($this->user_id && $edit->val && $edit->column && $edit->function->function_id) {
			//First update the actual function
			$sql = "UPDATE functions SET " . FunctionEdit::$columns[$edit->column] . " = '" . $edit->val . "' WHERE function_id = '" . $edit->function->function_id . "'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);

			if ($this->dbc->affected_rows == 1) { 
				//There was actually a change, log the edit
				$sql = "INSERT INTO `function_edits` (column_id, val, function_id, editor_id, rollback, date_edited) VALUES('$edit->column', '$edit->val', '" . $edit->function->function_id . "', '$this->user_id', 0, NOW())";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function rollback_function_edit(FunctionEdit $edit) {
		/*
		(FunctionEdit) -> Bool
		Given a function edit, updates the current function to the new edit and rollbacks
		 */
		if ($edit->edit_id && $this->user_id) {
			$sql = "SELECT column, val, function_id, edit_id FROM `function_edits` WHERE edit_id = '$edit->edit_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			if ($result->num_rows == 1) {

				//Update the function and log the edit
				$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
				$sql = "INSERT INTO function_edits (column_id, val, function_id, editor_id, rollback, date_edited) ('" . $row['column'] . "', '" . $row['val'] . "', '$this->user_id', 1, NOW())";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);

				//Now update the actual function
				$sql = "UPDATE functions SET " . FunctionEdit::$columns[$row['column']] . " = '" . $row['column'] . "' WHERE function_id = '" . $row['column'] . "'";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function log_edit(Edit $edit) {
		/*
		(Edit) -> Bool
		Attempts to log an edit that a user made into the database
		 */
		if ($this->user_id && !$edit->edit_id && $edit->linked_id) {
			$sql = "INSERT INTO edit (linked_id, type, date_edited, editor_id, changes) VALUES ('$edit->linked_id, '$edit->type', NOW(), '$this->user_id', '$edit->changes')";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public function log_login() {
		/*
		Updates the last time the user logged in
		 */
		if ($this->user_id) {
			$sql = "UPDATE users SET last_logged_in = NOW() WHERE user_id = '$this->user_id' LIMIT 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public static function get_author_by_result($result_id) {
		/*
		(int) -> User
		Returns the author is User object that authored the current referencing result id 
		 */
		$mysqli = Database::connection();
		$sql = "SELECT user_id FROM translation_results WHERE result_id = '$result_id'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		return mysqli_fetch_row($result)[0];
	}

	public function downvote_translation(OutgoingTranslation $translation) {
		/*
		The currently logged in user attempts to like the translation
		 */
		if ($translation->translation_id && $this->user_id) {
			//First fetch the author id of the translation
			$sql = "SELECT user_id FROM translation_results WHERE result_id = '$translation->translation_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$row_author = mysqli_fetch_array($result, MYSQLI_ASSOC);
			$author = new User($row_author);
			$sql = "INSERT INTO translation_feedback (result_id, user_id, status) VALUES ('$translation->translation_id', '$this->user_id', 0) ON DUPLICATE KEY UPDATE status = 0";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected = $this->dbc->affected_rows;
			switch ($affected) {
				case 0:
					//The translation was un-downvoted
					$author->change_reputation(10, 4, $translation->translation_id);
					$translation->decrement_downvotes();
					$sql = "DELETE FROM translation_feedback WHERE user_id = '$this->user_id' AND result_id = '$translation->translation_id' LIMIT 1";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					return true;
					break;
				case 1:
					//The translation was newly downvoted
					$author->change_reputation(-10, 2, $translation->translation_id);
					$translation->increment_downvotes();
					return true;
					break;
				case 2:
					//The translation was un-upvoted and downvoted
					$author->change_reputation(-10, 3, $translation->translation_id);
					$author->change_reputation(-10, 2, $translation->translation_id);
					$translation->decrement_upvotes();
					$translation->increment_downvotes();
					return true;
					break;
			}
		}
		else {
			return false;
		}
	}


	public function upvote_translation(OutgoingTranslation $translation) {
		/*
		The currently logged in user attempts to like the translation
		 */
		if ($translation->translation_id && $this->user_id) {
			//First fetch the author id of the translation
			$sql = "SELECT user_id FROM translation_results WHERE result_id = '$translation->translation_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$row_author = mysqli_fetch_array($result, MYSQLI_ASSOC);
			$author = new User($row_author);
			//Attempt to like
			$sql = "INSERT INTO translation_feedback (result_id, user_id, status) VALUES ('$translation->translation_id', '$this->user_id', 1) ON DUPLICATE KEY UPDATE status = 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected = $this->dbc->affected_rows;
			switch ($affected) {
				case 0:
					//This translation was already upvoted, un-upvote it
					$translation->decrement_upvotes();
					$sql = "DELETE FROM translation_feedback WHERE user_id = '$this->user_id' AND result_id = '$translation->translation_id' LIMIT 1"; //Might change this to 0 soon
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$author->change_reputation(-10, 3, $translation->translation_id);
					return true;
					break;
				case 1:
					//The translation was newly upvoted
					$translation->increment_upvotes();
					$author->change_reputation(10, 1, $translation->translation_id);
					return true;
				case 2:
					//The translation was once downvoted but is now upvoted, remove the previous penalty and add the new one
					$author->change_reputation(10, 4, $translation->translation_id);
					$author->change_reputation(10, 1, $translation->translation_id);
					$translation->decrement_downvotes();
					$translation->increment_upvotes();
					return true;
			}
		}
		else {
			return false;
		}
	}

	public function post_comment_on_translation(OutgoingTranslation $translation, TranslationComment $translation_comment) {
		if ($translation->result_id && $this->user_id) {
			//The translation is valid, insert the comment, assumes already sanitized
			$sql = "INSERT INTO translation_comments (author_id, comment_text, result_id, date_posted) VALUES ('$this->user_id', '$translation_comment->comment_text', '$translation->result_id', NOW())";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$comment_id = $this->dbc->insert_id;
			return $comment_id;
		}
		else {
			return false;
		}
	}

	public function post_comment_on_user_profile(User $user, ProfileComment $comment) {
		/*
		(User, ProfileComment) -> Mixed(ProfileComment/Bool)
		Posts a comment on a user's profile
		 */
		if ($this->user_id && $user->user_id) {
			$sql = "INSERT INTO profile_comments (comment_text, author_id, profile_id, date_posted) VALUES ('$comment->comment_text', '$this->user_id', '$user->user_id', NOW())";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$comment->comment_id = $this->dbc->insert_id;
			$comment->date_posted = date('M j, y');

			$comment->author = clone $this;
			return $comment;
		}
		else {
			return false;
		}
	}

	public function post_time_complexity() {
		throw new BadMethodCallException;
	}

	public function unupvote_upvote_translate(IncomingTranslation $translation) {
		/*
		Upvotes an incoming translation/translation request
		 */
		if ($this->user_id && $translation->translation_id) {
			$sql = "INSERT INTO translation_requests_feedback (user_id, translation_id, status) VALUES ('$this->user_id', '$translation->translation_id', 1)
			ON DUPLICATE KEY UPDATE feedback_id = feedback_id";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$affected_rows = $this->dbc->affected_rows;
			$sql = "SELECT user_id FROM translations WHERE translation_id = '$translation->translation_id'";
			$result_get_requester = $this->dbc->query($sql)
			or die ($this->dbc->error);
			$requester_id = mysqli_fetch_row($result_get_requester)[0];
			$requester = new User(array(
				'user_id' => $requester_id)
			);
			switch ($affected_rows) {
				case 0:
					$sql = "DELETE FROM translation_requests_feedback WHERE user_id = '$this->user_id' AND translation_id = '$translation->translation_id' LIMIT 1";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$translation->decrement_upvotes();
					$requester->change_reputation(-3, 16, $this->user_id);
					break;
				case 1:
					//Change the reputation
					$translation->increment_upvotes();
					$requester->change_reputation(3, 15, $this->user_id);
					break;
			}
			return ($affected_rows == 1) ? 1 : 2;
		}
		else {
			return false;
		}
	}

	public function delete_translation(OutgoingTranslation $translation) {
		/*
		Delete Translation
		 */
		if ($this->user_id && $translation->translation_id && is_bool($translation->single) && $translation->author->user_id == $this->user_id) {
			$result_id = $translation->translation_id;
			$sql = "DELETE FROM translation_results WHERE result_id = '$result_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			if ($translation->single) {
				$sql = "DELETE FROM translation_singled WHERE result_id = '$result_id'";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);
			}
			else {
				$sql = "DELETE FROM translation_multiple WHERE result_id = '$result_id'";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);
			}
			return true;
		}
		else {
			return false;
		}
	}

	public function log_profile_view() {
		/*
		Logs the current ip as viewed profile
		 */
		if ($this->user_id) {
			Database::clear_temp_views();	
			$ip = Database::sanitize($_SERVER['REMOTE_ADDR']);
			$sql = "INSERT INTO temp_views (ip_address, value_id, type, date) VALUES('$ip', '$this->user_id', 1, NOW())
			ON DUPLICATE KEY UPDATE ip_address = '$ip'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			if ($this->dbc->affected_rows == 1) {
				$sql = "UPDATE users SET views = views + 1 WHERE user_id = '$this->user_id' LIMIT 1";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);
			}
		}
	}

	public static function get_current_user_id() {
/*		echo 'Session is ' . $_SESSION['user_id'];
		echo 'Cookie is' . $_COOKIE['user_id'];
		echo 'Session is' . isset($_SESSION['user_id']);
		echo 'Cookie is' . isset($_COOKIE['user_id']);*/
		$mysqli = Database::connection();
		if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
			//Check for a cookie
			if (!empty($_COOKIE['token']) && is_numeric($_COOKIE['user_id'])) {
				$token = $_COOKIE['token'];
				$user_id = $_COOKIE['user_id'];
				$sql = "SELECT token FROM users WHERE user_id = '$user_id'";
				$result = $mysqli->query($sql)
				or die($mysqli->error);
				if ($result->num_rows == 1) {
					//Attempt to verify the token
					$token_hashed = mysqli_fetch_row($result)[0];
					$verify = password_verify($token, $token_hashed);
					if ($verify) {
						//Create the session again
						$_SESSION['user_id'] = $user_id;
						return $user_id;
					}
					else {
						return 'None';
					}
				}
				else {
					return 'None';
				}
			}
			else {
				return 'None';
			}
		}
		else {
			return $_SESSION['user_id'];
		}
	}
}
