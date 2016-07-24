<?php
/*require_once __DIR__ . '/Database.php';
require_once __DIR__ . "/User.php";
require_once __DIR__ . '/TranslationActivity.php';*/
require_once __DIR__ . '/../vars/constants.php';

class TranslateFactory {
	/*
	Class for the translate factory 
	 */
	const DIRECT_PATH = 'Both functions are direct-pathed with each other (closest relationship possible)';
	const SAME_CATEGORY = 'Both functions are placed under the same parent and super parent category. Category description is {{category_description}}';
	const UPVOTES = 'Has more than {{x}} upvotes';

	private $dbc;
	public static $user_id = 1, $translation_id;

	public function __autoload($name) {
		/*
		PHP Magic Method for autoloading
		 */
		try {
			$result = require_once "$name.php";
			if (!$result) {
				throw new UnloadableClass('Could not load class name ' . $name);
			}
			return true;
		}
		catch (UnloadableClass $e) {
			self::print_exception($e);
			return false;
		}
	}
	
	public static function translate(translate $translate, $single = false, $load_comments = false) {
		/*
		(Translate) -> Array of Outgoing Translations
		Attempts to translate a function while building the translations table as well
		 */
		$mysqli = Database::connection();
		if ($translate->from_function->function_language->language_id) {
			if ($translate->from_function->function_language->language_id != $translate->to_language->language_id) {
				$deploy = true;
			}
			else {
				$deploy = false;
			}
		}
		else {
			$sql = "SELECT language FROM functions WHERE function_id = '" . $translate->from_function->function_id . "'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$language_id = mysqli_fetch_row($result)[0];
			if ($language_id && ($language_id != $translate->to_language->language_id)) {
				$deploy = true;
			}
			else {
				$deploy = false;
			}
		}
		if (!$deploy) {
			return false;
		}
		self::existence($translate); //To check if this translation was performed before to store it

		$translation_id = self::$translation_id;

		$temp_translation = new OutgoingTranslation(array(
			'translation_id' => self::$translation_id)
		);
		$temp_translation->log_translation_views();

		$to_id = $translate->to_language->language_id;
		$category_id = $translate->from_function->category_id;

/*		echo $category_id;
		echo $to_id;*/

		//Planning to depreciate
		//Nevermind, just depreciated now

/*		$sql = "SELECT t1.function_id , t1.function_name, t1.category_id
		FROM functions as t1
		WHERE t1.category_id = '$category_id' 
		AND t1.language = '$to_id'
		AND NOT EXISTS (
			SELECT t2.result_id
			FROM translation_singled as t2
			INNER JOIN translation_results as t3
			ON t3.result_id = t2.result_id
			AND t3.translation_id = " . self::$translation_id . "
			WHERE t2.suggested_id = t1.function_id
		)";
	//	echo $sql;
		$result_fetch = $mysqli->query($sql)
		or die ($mysqli->error);
		while ($row = mysqli_fetch_array($result_fetch)) {
			$suggested_function = new LanguageFunction($row);
			//Now build the translations table
			$sql = "INSERT INTO translation_results (translation_id, type, user_id) VALUES ('" . self::$translation_id .  "', '" . SINGLE_TYPE . "', '" . self::$user_id . "')
			ON DUPLICATE KEY UPDATE result_id = result_id";
			//echo $sql;
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
		//	echo 'updated';
			if ($mysqli->affected_rows == 1) {
				//echo 'inserted';
				$id = $mysqli->insert_id;
				$sql_insert = "INSERT INTO translation_singled (suggested_id, result_id) VALUES ('" . $suggested_function->function_id . "', '" . $id . "')
				ON DUPLICATE KEY UPDATE result_id = result_id";
				//echo $sql_insert;
				$result = $mysqli->query($sql_insert)
				or die ($mysqli->error);
			}
		}*/

		//Now select the best translations, first select the aggregate results
		
		$user_id = User::get_current_user_id();
		
		$sql = "(SELECT null as function_id, t1.suggested_method as function_name, t2.upvotes as upvotes, t2.downvotes as downvotes, t2.user_id as user_id, t5.username as username, t1.result_id as result_id, null as category_id, null as category_description, null as function_description, null as link, null as syntax, t2.comment as comment, t6.status, t2.type, t5.avatar, t5.points, DATE_FORMAT(t2.date_posted, '%b %e, %Y') as `date_posted`
		FROM translation_multiple as t1
		RIGHT JOIN translation_results as t2
		ON t2.result_id = t1.result_id
		LEFT JOIN users as t5
		ON t5.user_id = t2.user_id
		LEFT JOIN translation_feedback as t6
		ON t6.result_id = t1.result_id
		AND t6.user_id = '$user_id'
		WHERE t2.translation_id = " . self::$translation_id . "
		AND t1.result_id IS NOT NULL
		ORDER BY (t2.upvotes - t2.downvotes) DESC)
		UNION
		(SELECT tx.suggested_id as function_id, t2.function_name, t1.upvotes as upvotes, t1.downvotes as downvotes, t1.user_id, t5.username as username, t1.result_id, t2.category_id, t3.description, t2.description, t2.link, t2.syntax, t1.comment as comment, t6.status, t1.type, t5.avatar, t5.points, DATE_FORMAT(t1.date_posted, '%b %e, %Y') as `date_posted`
		FROM translation_results as t1
		INNER JOIN translation_singled as tx
		ON tx.result_id = t1.result_id
		LEFT JOIN users as t5
		ON t5.user_id = t1.user_id
		LEFT JOIN functions as t2
		ON tx.suggested_id = t2.function_id
		LEFT JOIN category as t3
		ON t3.category_id = t2.category_id
		LEFT JOIN translation_feedback as t6
		ON t6.result_id = t1.result_id
		AND t6.user_id = '$user_id'
		WHERE t1.translation_id = '" . self::$translation_id . 
		"' ORDER BY (t1.upvotes - t1.downvotes) DESC)
		UNION
		(SELECT null as `function_id`, null as `function_name`, t1.upvotes as upvotes, t1.downvotes as downvotes, t1.user_id, t5.username as username, t1.result_id, null as `category_id`, null as `description`, null as `t2.description`, null as 	`link`, null as `syntax`, t1.comment as comment, t6.status, t1.type, t5.avatar, t5.points, DATE_FORMAT(t1.date_posted, '%b %e, %Y') as `date_posted`
		FROM translation_results as t1
		LEFT JOIN users as t5
		ON t5.user_id = t1.user_id
		LEFT JOIN translation_feedback as t6
		ON t6.result_id = t1.result_id
		AND t6.user_id = '$user_id'
		WHERE t1.translation_id = '" . self::$translation_id . 
		"' AND t1.type = 2 
		ORDER BY (t1.upvotes - t1.downvotes) DESC)
		";
		//echo $sql;
		$sql = ($single) ? $sql . ' LIMIT 1' : $sql;
		//echo $sql;
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
/*
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

		}*/
/*
		$sql = "SELECT tx.suggested_id as function_id, t1.upvotes as upvotes, t1.downvotes as downvotes, t2.function_name, tx.user_id, t1.result_id, t2.category_id, t3.description, t2.description, t2.link, t2.syntax
		FROM translation_results as t1
		LEFT JOIN translation_singled as tx
		ON tx.result_id = t1.result_id
		LEFT JOIN functions as t2
		ON tx.suggested_id = t2.function_id
		LEFT JOIN category as t3
		ON t3.category_id = t2.category_id
		WHERE t1.translation_id = '" . self::$translation_id . 
		"' ORDER BY (t1.user_id =1) DESC, (t1.upvotes - t1.downvotes) DESC";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
*/
		$translations_completed = array();

		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$row_comments = array(); //Holding array for the comments to this translation
			if ($load_comments) {
				//Load the comments for this translation
				$sql_load_comments = "SELECT t1.comment_id, t1.comment_text, t1.author_id, DATE_FORMAT(t1.date_posted, '%M %e, %Y') as date_posted , t2.username, t2.avatar
				FROM translation_comments as t1 
				LEFT JOIN users as t2
				ON t2.user_id = t1.author_id
				WHERE t1.result_id = '" . $row['result_id']. "'";
				$result_load_comments = $mysqli->query($sql_load_comments)
				or die ($mysqli->error);
				while ($row_comment = mysqli_fetch_array($result_load_comments, MYSQLI_ASSOC)) {
					$row_comment['comment_text'] = $row_comment['comment_text'];
					$row_comment['author'] = new User(array(
						'user_id' => $row_comment['author_id'],
						'username' => $row_comment['username'],
						'avatar' => $row_comment['avatar'])
					);
					$comment = new TranslationComment($row_comment);
					$row_comments[] = $comment;
				}
			}
			if ($row['type'] == 2) {
				//This is note, parse as a note
				
				//Create the author object
				$row['author'] = new User(array(
					'user_id' => $row['user_id'],
					'username' => $row['username'],
					'avatar' => $row['avatar'],
					'points' => $row['points'])
				);
				$translation = new OutgoingTranslation(array(
					'result_id' => $row['result_id'],
					'upvotes' => $row['upvotes'],
					'downvotes' => $row['downvotes'],
					'from_function' => $translate->from_function,
					'to_language' => $translate->to_language,
					'database_answer' => $answer,
					'reasons' => $reasons,
					'comment' => $row['comment'],
					'author' => $row['author'],
					'translation_id' => self::$translation_id,
					'comments' => $row_comments,
					'status' => $row['status'],
					'date_posted' => $row['date_posted'],
					'note' => true
					)
				);
				$translation->type = 'note';
				array_push($translations_completed, $translation);
				continue;
			}
			if ($row['function_name']) {
				$reasons = array();
				$answer = ($row['user_id'] == 1) ? true: false;
				if ($row['function_id']) {
					//Checking if it a single function so we can enumerate the reasons
					$reasons = array();
					if ($answer) {
						//Checking if it is a database answer, create reason block here ---------------
						if ($row['category_id'] == $translate->from_function->category_id) {
							array_push($reasons, self::DIRECT_PATH);
							$search = '{{category_description}}';
							$reason_type = self::SAME_CATEGORY;
							$reason = str_replace($search, $row['category_description'], $reason_type);
							array_push($reasons, $reason);
						}
						if ($row['upvotes'] > UPVOTES_THRESHOLD) {
							array_push($reasons, $reason);
						}
					}
				}

				//Create the author object
				$row['author'] = new User(array(
					'user_id' => $row['user_id'],
					'username' => $row['username'],
					'avatar' => $row['avatar'],
					'points' => $row['points'])
				);
				//All non-note types have a function name attached to them
				$suggested_function = new LanguageFunction(array(
					'function_id' => $row['function_id'],
					'function_name' => $row['function_name'],
					'language' => $translate->to_language,
					'description' => $row['function_description'],
					'link' => $row['link'],
					'syntax' => $row['syntax'])
				);
				$translation = new OutgoingTranslation(array(
					'result_id' => $row['result_id'],
					'suggested_function' => $suggested_function,
					'upvotes' => $row['upvotes'],
					'downvotes' => $row['downvotes'],
					'from_function' => $translate->from_function,
					'to_language' => $translate->to_language,
					'database_answer' => $answer,
					'reasons' => $reasons,
					'comment' => $row['comment'],
					'author' => $row['author'],
					'translation_id' => self::$translation_id,
					'comments' => $row_comments,
					'status' => $row['status'],
					'date_posted' => $row['date_posted'],
					'note' => false
					)
				);
				array_push($translations_completed, $translation);
			}
		}
		return $translations_completed;		
	}

	public static function existence(Translate $translate) {
		/*
		Checks against the translations table to see if this translation was performed prior and tries to get the id to make the translation faster. It populates the static variable for the current translation id. Also stores the user id that is currently logged as the person who requested it
		(Null) -> Null
		 */
		$mysqli = Database::connection();
		//require_once '../../includes/User.php';

		$user_id = User::get_current_user_id();
		//First check if the translation_id exists
		$from_id = $translate->from_function->function_id;
		$out_id = $translate->to_language->language_id;
		$sql = "SELECT translation_id FROM translations WHERE from_function_id = '$from_id'
		AND to_language_id = '$out_id'";
		//echo $sql;
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if (mysqli_num_rows($result) == 1) {
			$id = mysqli_fetch_row($result)[0];
		}
		else {
			$insert = "INSERT INTO translations (from_function_id, to_language_id, date, last_updated, linked_id, user_id, action_type) VALUES ('$from_id', '$out_id', NOW(), NOW(), IF('$user_id' != 'None', '$user_id', 3), IF('$user_id' != 'None', '$user_id', 3), " . REQUESTED_ACTIVITY_TYPE .")";
			$result = $mysqli->query($insert)
			or die ($mysqli->error);
			$id = mysqli_insert_id($mysqli);
		}
		self::$translation_id = $id;
		return $id;
	}

	public static function resolve_translations() {
		/*
		Builds the translations table over again
		 */
		
	}
}

abstract class Translate {
	private $dbc;
	
	public $from_function, $to_language;

	abstract public function increment_upvotes();
	abstract public function increment_downvotes();
	abstract public function decrement_upvotes();
	abstract public function decrement_downvotes();

	public static $action_classes = array('Translate' => 0, 'Comment' => 1, 'User' => 2); //The appropiate classes that an action can be for updating the activity of a translation

	public static $activity_subjects = array(
		0 => '{{timeMessage}} ago - {{username}} submitted a new translation', //The possible messages for the last activity message. Translation type can be translation or note
		1 => '{{timeMessage}} ago - {{username}} added a new note',
		2 => '{{timeMessage}} ago - {{username}} upvoted',
		3 => '{{timeMessage}} ago - {{username}} requested this translation'
	);

	public static $activity_searches = array(
		0 => array('{{timeMessage}}', '{{username}}'),
		1 => array('{{timeMessage}}', '{{username}}'),
		2 => array('{{timeMessage}}', '{{username}}'),
		3 => array('{{timeMessage}}', '{{username}}')
	);

	public static function log_update(TranslationActivity $action) {
		/*
		Logs an updated action on
		 */
		try {
			if ($action->identifier || $action->identifier == 0) {
				if (array_key_exists($action->identifier, self::$activity_subjects)) {
					//The action fits within the expected parameters
					$mysqli = Database::connection();
					$sql = "UPDATE translations SET last_updated = NOW(), linked_id = '$action->linked_id', action_type = '$action->identifier' WHERE translation_id = '$action->translation_id'";
					$result = $mysqli->query($sql)
					or die ($mysqli->error);
					return true;
				}
				else {
					throw new UnexpectedValueException('UnexpectedValueException occured on request on activity update');
				}
			}
			else {
				throw new OutOfBoundsException('OutOfBoundsException occured on request');
			}
		}
		catch (UnexpectedValueException $e) {
			Database::print_exception($e);
			return false;
		}
		catch (OutOfBoundsException $e) {
			Database::print_exception($e);
			return false;
		}
	}

	public function resolve_activity_message() {
		/*
		Resolves the activity message based on the property last_active
		 */
		if ($this->last_activity) {
			//Get the message
			$subject = self::$activity_subjects[$this->last_activity->identifier];
			$search = self::$activity_searches[$this->last_activity->identifier];
			$this->dbc = Database::connection();
			switch ($this->last_activity->identifier) {
				case 0:
					//A user has posted a note or submited a translation in this translate, build the array to replace the search field
					$user_id = $this->last_activity->linked_id;
					$sql = "SELECT username FROM users WHERE user_id = '$user_id' LIMIT 1";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$username = mysqli_fetch_row($result)[0];
					$replace = array($this->last_activity->action_date, $username);
					break;
				case 1:
					//The user has posted a new note in this translation request
					$user_id = $this->last_activity->linked_id;
					$sql = "SELECT username FROM users WHERE user_id = '$user_id' LIMIT 1";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$username = mysqli_fetch_row($result)[0];
					$replace = array($this->last_activity->action_date, $username);
					break;
				case 2:
					//The user upvoted this translation
					$user_id = $this->last_activity->linked_id;
					$sql = "SELECT username FROM users WHERE user_id = '$user_id' LIMIT 1";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$username = mysqli_fetch_row($result)[0];
					$replace = array($this->last_activity->action_date, $username);
					break;
				case 3:
					//The user requested this translation
					$user_id = $this->last_activity->linked_id;
					$sql = "SELECT username FROM users WHERE user_id = '$user_id' LIMIT 1";
					$result = $this->dbc->query($sql)
					or die ($this->dbc->error);
					$username = mysqli_fetch_row($result)[0];
					$replace = array($this->last_activity->action_date, $username);
					break;
			}
			//Now create the message
			$this->last_activity_message = str_replace($search, $replace, $subject);
		}
	}

	final public function log_translation_views() {
		/*
		Logs that the translation was viewed
		 */
		if ($this->translation_id) {
			$this->dbc = (is_null($this->dbc)) ? Database::connection() : $this->dbc;
			Database::clear_temp_views();
			$ip = $_SERVER['REMOTE_ADDR'];
			$sql = "INSERT INTO temp_views (ip_address, value_id, type, date) VALUES('$ip', '$this->translation_id', 2, NOW())
			ON DUPLICATE KEY UPDATE ip_address = '$ip'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			if ($this->dbc->affected_rows == 1) {
				$sql = "UPDATE translations SET views = views + 1 WHERE translation_id = '$this->translation_id' LIMIT 1";
				$result = $this->dbc->query($sql)
				or die ($this->dbc->error);
			}
		}
	}

	final protected function __autoload($name) {
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


class OutgoingTranslation extends Translate {
	/*
	Class for a completed translate
	 */
	private $dbc;
	
	public $translation_id, $suggested_function, $from_function, $to_langauge, $upvotes, $downvotes, $comments, $date_posted, $note;

	public static $defaults = array(
		'result_id' => null,
		'suggested_function' => array(),
		'from_function' => null,
		'to_language' => null,
		'upvotes' => 0,
		'downvotes' => 0,
		'database_answer' => false,
		'reasons' => array(),
		'comment' => null,
		'author' => null,
		'translation_id' => null,
		'upvoted' => false,
		'downvoted' => false,
		'status' => null,
		'comments' => array(),
		'date_posted' => null,
		'note' => false
		);

	public function __construct(array $args = array()) {
		/*
		Constructs the completed translated object
		 */
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Construct the object properites
		$this->dbc = Database::connection();

		$this->result_id = (is_numeric($args['result_id'])) ? $args['result_id'] : null;
		$this->suggested_function = (is_a($args['suggested_function'], 'LanguageFunction')) ? $args['suggested_function'] : array();
		$this->from_function = (is_a($args['from_function'], 'LanguageFunction')) ? $args['from_function'] : null;
		$this->to_language = (is_a($args['to_language'], 'Language')) ? $args['to_language'] : null;
		$this->upvotes = (is_numeric($args['upvotes'])) ? $args['upvotes'] : 0;
		$this->downvotes = (is_numeric($args['downvotes'])) ? $args['downvotes'] : 0;
		$this->database_answer = (is_bool($args['database_answer'])) ? $args['database_answer'] : false;
		$this->reasons = (is_array($args['reasons'])) ? $args['reasons'] : array();
		$this->single = (isset($this->suggested_function->function_id) && $this->suggested_function->function_id)? true: false;
		$this->comment = $args['comment'];
		$this->author = (is_a($args['author'], 'User')) ? $args['author'] : null;
		$this->translation_id = (is_numeric($args['translation_id'])) ? $args['translation_id'] : null;
		$this->comments = (is_array($args['comments'])) ? $args['comments'] : array();
		if (!is_null($args['status'])) {
			if ($args['status'] == 1) {
				$this->upvoted = true;
			}
			elseif ($args['status'] == 0) {
				$this->downvoted = true;
			}
		}
		else {
			$this->downvoted = false;
			$this->upvoted = false;
		}
		$this->date_posted = $args['date_posted'];
		$this->note = (is_bool($args['note'])) ? $args['note'] : false;
	}

	public function increment_upvotes() {
		/*
		Increments the upvotes for this translation answer
		 */
		if ($this->translation_id) {
			$sql = "UPDATE translation_results SET upvotes = upvotes + 1 WHERE result_id = '$this->translation_id' LIMIT 1";
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
		Increments the upvotes for this translation answer
		 */
		if ($this->translation_id) {
			$sql = "UPDATE translation_results SET upvotes = upvotes - 1 WHERE result_id = '$this->translation_id' LIMIT 1";
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
		Increments the upvotes for this translation answer
		 */
		if ($this->translation_id) {
			$sql = "UPDATE translation_results SET downvotes = downvotes + 1 WHERE result_id = '$this->translation_id' LIMIT 1";
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
		Increments the upvotes for this translation answer
		 */
		if ($this->translation_id) {
			$sql = "UPDATE translation_results SET downvotes = downvotes - 1 WHERE result_id = '$this->translation_id' LIMIT 1";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}
}


class IncomingTranslation extends Translate {
	public $from_function, $language1, $language2, $translation_id;

	public static $defaults = array(
		'translation_id' => null,
		'from_function' => null,
		'to_language' => null
		);

	public function __construct(array $args) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		//Assign the object properties
		$this->dbc = Database::connection();

		$this->translation_id = (is_numeric($args['translation_id'])) ? $args['translation_id'] : null;
		$this->from_function = (is_a($args['from_function'], 'LanguageFunction')) ? $args['from_function'] : null;
		$this->to_language = (is_a($args['to_language'], 'Language')) ? $args['to_language'] : null;

	}

	public function increment_upvotes() {
		/*
		Increments the upvotes for this translation request
		 */
		if ($this->translation_id) {
			$sql = "UPDATE translations SET upvotes = upvotes + 1 WHERE translation_id = '$this->translation_id'";
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
		Decrements the upvotes for this translation
		 */
		if ($this->translation_id) {
			$sql = "UPDATE translations SET upvotes = upvotes - 1 WHERE translation_id = '$this->translation_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}

	public function decrement_downvotes() {
		try {
			throw new BadMethodCallException('Not yet implement');
		}
		catch (BadMethodCallException $e) {
			Database::print_exception($e);
		}
	}

	public function increment_downvotes() {
		try {
			throw new BadMethodCallException('Not yet implement');
		}
		catch (BadMethodCallException $e) {
			Database::print_exception($e);
		}
	}
}