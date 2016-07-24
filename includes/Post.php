<?php
//Generic class for a post on Paraphrase

class Post {
	private $dbc;
	public $post_id, $post_text, $author, $date_posted;

	public static $defaults = array(
		'post_id' => null,
		'post_text' => null,
		'author_id' => null,
		'date_posted' => null,
		'post_title' => null,
		'match_score' => 0 //For searching
	);

	public function __construct(array $args = array()) {
		$defaults = self::$defaults;
		$args = array_merge($defaults, $args);

		$this->dbc = Database::connection();
		$this->post_id = (is_numeric($args['post_id'])) ? $args['post_id'] : null;
		$this->post_text = $args['post_text'];
		$this->author = new User(array(
			'user_id' => $author_id)
		);
		$this->date_posted = $args['date_posted'];
		$this->post_title = $args['post_title'];
		$this->match_score = floatval($args['match_score']);
	}

	public static function get_author_id_by_post_id($post_id) {
		/*
		(Int) -> Int/Bool
		Gets the author id by the post id
		 */
		if (is_numeric($post_id)) {
			$mysqli = Database::connection();
			$sql = "SELECT author_id FROM posts WHERE post_id = '$post_id'";
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			return mysqli_fetch_row($result)[0];
		}
		else {
			return false;
		}
	}

	public function increment_upvotes() {
		/*
		(Null) -> Null
		Increments the upvotes on this post
		 */
		if ($this->post_id) {
			$sql = "UPDATE posts SET upvotes = upvotes + 1 WHERE post_id = '$this->post_id'";
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
		Decrements the upvotes on this post
		 */
		if ($this->post_id) {
			$sql = "UPDATE posts SET upvotes = upvotes - 1 WHERE post_id = '$this->post_id'";
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
		Increments the upvotes on this post
		 */
		if ($this->post_id) {
			$sql = "UPDATE posts SET downvotes = downvotes + 1 WHERE post_id = '$this->post_id'";
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
		Decrements the upvotes on this post
		 */
		if ($this->post_id) {
			$sql = "UPDATE posts SET downvotes = downvotes - 1 WHERE post_id = '$this->post_id'";
			$result = $this->dbc->query($sql)
			or die ($this->dbc->error);
			return true;
		}
		else {
			return false;
		}
	}
}