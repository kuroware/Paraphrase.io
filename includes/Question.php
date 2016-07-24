<?php

class Question {

	private $dbc;
	public $question_id, $title, $body, $author, $date_posted;

	public static $defaults = array(
		'question_id' => null,
		'title' => null,
		'body' => null,
		'author' => null,
		'date_posted' => null,
		'src_language' => null,
		'des_language' => null,
		'tagged_language' => null,
		'date_posted_ago' => null,
		'status' => null,
		'downvoted' => false,
		'upvoted' => false,
		'upvotes' => 0,
		'downvotes' => 0,
		'match_score' => 0
	);

	public static function get_author_by_question(Question $question) {
		/*
		(Question) -> Mixed (Bool/User)
		With a given question and a valid question_id, returns the User object associated to this question
		Returns false if the question is not found or there is no associated author
		 */
		if ($question->question_id) {
			$mysqli = Database::connection();
			$sql = "SELECT t2.user_id
			FROM questions as t1
			INNER JOIN users as t2 
			ON t2.user_id = t1.author_id
			WHERE t1.question_id = '$question->question_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			if ($result->num_rows == 1) {
				$user_id = mysqli_fetch_row($result)[0];
				$user = new User(array(
					'user_id' => $user_id)
				);
				return $user;
			}
			else {
				return false;
			}
		}
	}

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Assign the object properties
		$this->dbc = Database::connection();
		$this->question_id = (is_numeric($args['question_id'])) ? $args['question_id'] : null;
		$this->title = $args['title'];
		$this->body = $args['body'];
		$this->author = (is_a($args['author'], 'User')) ? $args['author'] : null;
		$this->date_posted = $args['date_posted'];
		$this->src_language = (is_a($args['src_language'], 'Language')) ? $args['src_language'] : null;
		$this->des_language = (is_a($args['des_language'], 'Language')) ? $args['des_language'] : null;
		if (is_numeric($args['date_posted_ago'])) {
			$this->date_posted_ago = Database::secondstoTime($args['date_posted_ago']);
		}
		if ($args['status'] == null) {
			$this->upvoted = false;
			$this->downvoted = false;
		}
		else {
			if ($args['status'] == 1) {
				$this->upvoted = true;
				$this->downvoted = false;
			}
			elseif ($args['status'] == 2) {
				$this->upvoted = false;
				$this->downvoted = true;
			}
		}
		$this->upvotes = (is_numeric($args['upvotes'])) ? $args['upvotes'] : 0;
		$this->downvotes = (is_numeric($args['downvotes'])) ? $args['downvotes'] : 0;
		$this->match_score = floatval($args['match_score']);

		$formatted_string_date = ''; //The temporary variable to hold the formatted string date
		if ($this->date_posted_ago) {
			//echo $this->action_date;
			//Attempt to style the action date
			$x = explode(', ', $this->date_posted_ago);
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
			$this->date_posted_ago = $formatted_string_date;
		}
	}

	public function increment_upvotes() {
		/*
		(Null) -> Null
		Attempts to increment the upvotes for this question
		 */
		if ($this->question_id) {
			$sql = " UPDATE questions SET upvotes = upvotes + 1 WHERE question_id = '$this->question_id' LIMIT 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public function decrement_upvotes() {
		/*
		(Null) -> Null
		Attempts to decrement the upvotes for this question
		 */
		if ($this->question_id) {
			$sql = " UPDATE questions SET upvotes = upvotes - 1 WHERE question_id = '$this->question_id' LIMIT 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public function increment_downvotes() {
		/*
		(Null) -> Null
		Attempts to increment the downvotes for this question
		 */
		if ($this->question_id) {
			$sql = " UPDATE questions SET downvotes = downvotes + 1 WHERE question_id = '$this->question_id' LIMIT 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public function decrement_downvotes() {
		/*
		(Null) -> Null
		Attempts to decrement the downvotes for this question
		 */
		if ($this->question_id) {
			$sql = " UPDATE questions SET downvotes = downvotes - 1 WHERE question_id = '$this->question_id' LIMIT 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public function log_edit(User $user) {
		/*
		(User) -> Bool
		Attempts to log an edit that the current logged in user made
		 */
		if ($user->user_id && $this->question_id) {
			$sql = "INSERT INTO question_edit_history (user_id, question_id, date_edited) VALUES ('$user->user_id', '$this->question_id', NOW())";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}
}