<?php
error_reporting(E_ALL);
session_start();
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
require_once 'navbar.php';
$mysqli = Database::connection(); //MySQL connection variable
$original_search_phrase = $_GET['search_phrase'];
$search_phrase = Database::sanitize($_GET['search_phrase']); //Holding variable for the search phrase
$language = (ctype_digit($_GET['src']) && (intval($_GET['src']) > 0) && (intval($_GET['src']) <= 10)) ? $_GET['src'] : 11; //The source that is being searched for - language id
//Fetch translations done today
$stats = Database::pull_search_stats_today();
$translations_today = $stats['translations_done_today'];
$new_translations = $stats['new_translation_requests_today'];
$from_functions = array(); //By default, array is 0

//---------------------------------------Parsing for destination language specifics -----------------------------------------
if ($language <= 10) {
	//There was a search request done for some desintation language. Parse via two possiblities:
	// 1. Search could be a missed translation, parse for a possible translation and for other search results
	// 2. Normal search result, parse for search result
	
	//The search could have been a missed translation, check for it
	$possible_function_phrase = $search_phrase;
	if (false) {
		//On development hold, the translation auto suggestor
		if (!strpos($possible_function_phrase, ' ')) {
			//This could be a possible translate request since the search phrase has no space and the destination language was selected, try to see if there could be any possible translations available
			
			$translation = true; //A translation was checked for

			//First check if this is a possible translation request, which should be only 
			$possible_function_phrase = trim($search_phrase);
			$possible_function_phrase = Database::sanitize(trim($search_phrase, '.')); //Since dot access attribute is not included in indexed function names
			$possible_function_phrase = trim($possible_function_phrase, '()');

			$sql = "SELECT language_name FROM languages WHERE language_id = '$language'";
			$result_fetch_language = $mysqli->query($sql)
			or die ($mysqli->error);
			$to_language_name = mysqli_fetch_row($result_fetch_language)[0];

			$to_language = new Language(array(
				'language_id' => $language,
				'language_name' => $to_language_name)
			); //The language the user selected the destination for

			//Check if the search phrase is a function that exists in the database
			$sql = "SELECT t1.function_id as `from_function_id`, t1.language, t1.function_name as `from_function_name`, t1.syntax, t1.description, t1.category_id, t2.language_name as `from_language_name`, big_o_average, big_o_best_case, big_o_worst_case, big_o_average_summary, t1.language as `from_language_id`
			FROM functions as t1
			LEFT JOIN languages as t2
			ON t2.language_id = t1.language
			WHERE t1.function_name = '$possible_function_phrase'";
			//echo $sql;
			$result = $mysqli->query($sql)
			or die ($mysqli->error);
			$result_rows = $result->num_rows;
			if ($result_rows > 0) {
				$correction = false;
			}
			else {
				//There were no functions found for the first possiblity, remove any extraneous characters and create a second possibilty the user could have been searching for and check to see if it exists in our database
				$second_possiblity = (!strpos($possible_function_phrase, '()')) ? $possible_function_phrase . '()' : rtrim($possible_function_phrase, '()');
				$sql = "SELECT t1.function_id as `from_function_id`, t1.language as `from_language_id`, t1.function_name as `from_function_name`, t1.syntax, t1.description, t1.category_id, t2.language_name as `from_language_name`, big_o_average, big_o_best_case, big_o_worst_case, big_o_average_summary
				FROM functions as t1
				LEFT JOIN languages as t2
				ON t2.language_id = t1.language
				WHERE t1.function_name = '$second_possiblity'";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				$result_rows = $result->num_rows;
				$correction = ($result_rows) ? true : false; //If no rows were found then there correction was false and now resuls were ever found for this function
			}
			if ($result_rows) {
				//Possible translation found
				$translations = array();
				$from_functions = array(); //Going to depreciate this in favor of direct from function id

				while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
					//Since function names are non-unique go through all the languages with the function names that match this possible search request
					
					//First make sure the from function language id is NOT equal to the to language id
					
					if ($row['from_language_id'] != $to_language->language_id) {
					
						$from_language_id = $row['from_language_id']; //The language id the possible function is from
						$from_function_id = $row['from_function_id'];

						//$mysqli->set_charset('utf8'); //No longer forced since not outputting in JSON

				/*		$sql = "SELECT language, category_id FROM functions WHERE function_id = '$from_function_id'";
						$result = $mysqli->query($sql)
						or die ($mysqli->error);

						if ($result->num_rows == 1) {
							$row = mysqli_fetch_row($result);
							$lan1 = $row[0];
							$category_id = $row[1];
						}
						else {
							throw new OutOfRangeException('OutOfRangeException occured on request');
						}*/

						$from_language = new Language(array(
							'language_id' => $from_language_id,
							'language_name' => $row['from_language_name'])
						);

						$from_function = new LanguageFunction(array(
							'function_id' => $row['from_function_id'],
							'function_name' => $row['from_function_name'],
							'syntax' => $row['syntax'],
							'description' => $row['description'],
							'big_o_average' => $row['big_o_average'],
							'big_o_average_summary' => $row['big_o_average_summary'] 
							)
						);
						$from_function->function_language = $from_language;

			/*			$from_function = new LanguageFunction(array(
							'function_id' => $from_function_id,
							'function_language' => $from_language, 
							'category_id' => $category_id)
						);*/
						$incoming_translation = new IncomingTranslation(array(
							'from_function' => $from_function,
							'to_language' => $to_language)
						);

						$from_functions[] = $from_function;

						//Now attempt the translations
						$translation_x = TranslateFactory::translate($incoming_translation, true); //Limit to only the top result for this translation
						$translations[] = $translation_x;
						//echo $translations[0]->reasons;
						//print_r($translations);
						//Finally log that this translation took place		
						Database::log_translation();
						//It's a translation request, attempt to parse it using 
			/*			header("Location: http://paraphrase.io/translate.php?lan1=$lan1&lan2=$language&src=$search_phrase");*/
					}
				}
			}
		}
	}

	//Parse as if the search was not a translation request
	$sql = "
	SELECT t1.description, t1.function_name, t1.function_id, MATCH(t1.description) AGAINST ('$search_phrase') + MATCH(t1.function_name) AGAINST ('$search_phrase') as `match_score`, t4.language_name
	FROM functions as t1
	LEFT JOIN category as t2
	ON t2.category_id = t1.category_id
	LEFT JOIN super_category as t3
	ON t3.super_id = t2.type
	LEFT JOIN languages as t4 
	ON t4.language_id = t1.language
	WHERE t1.language = '$language' AND (
		MATCH(t1.function_name) AGAINST ('$search_phrase')
		OR MATCH(t1.description) AGAINST ('$search_phrase')
	)
	OR t1.function_name = '$search_phrase'
 	ORDER BY match_score DESC";
 	//echo $sql;
	$result_functions = $mysqli->query($sql)
	or die ($mysqli->error);

	//Parse other translation answers now, if it was not a potential translation request
/*	if (strpos($search_phrase, ' ')) {
		$sql_search_questions = "SELECT t1.translation_id, t1.result_id, t1.type, t1.upvotes, t1.downvotes, MATCH(t1.comment) AGAINST ('$search_phrase'), t1.date_posted, t2.suggested_method
		FROM translation_results as t1
		INNER JOIN translation_multiple as t2
		ON t2.result_id = t1.result_id
		LEFT JOIN users as t5
		ON t5.user_id = t1.user_id
		WHERE MATCH(t1.comment) AGAINST ('$search_phrase')
		ORDER BY (t1.upvotes - t1.downvotes) DESC
		";
		$result_search_questions = $mysqli->query($sql_search_questions)
		or die ($mysqli->error);
	}*/
}
else {
	//The search is a site-wide search for some function or topic or concept or something
	$language = false;
	$sql = "
	SELECT t1.description, t1.function_name, t1.function_id, MATCH(t1.description) AGAINST ('$search_phrase') as `tbl1_score`, MATCH(t2.description) AGAINST ('$search_phrase') as `tbl2_score`, MATCH(t3.description) AGAINST ('$search_phrase') as `tbl3_score`, MATCH(t1.function_name) AGAINST ('$search_phrase') *3 + (MATCH(t1.description) AGAINST ('$search_phrase') * 2 + MATCH(t2.description) AGAINST ('$search_phrase') *1.5 + MATCH(t3.description) AGAINST ('$search_phrase')) * 0.5 as `match_score`, t4.language_name
	FROM functions as t1
	LEFT JOIN category as t2
	ON t2.category_id = t1.category_id
	LEFT JOIN super_category as t3
	ON t3.super_id = t2.type
	LEFT JOIN languages as t4 
	ON t4.language_id = t1.language
	WHERE
		MATCH(t1.function_name) AGAINST ('$search_phrase')
		OR MATCH(t1.description) AGAINST ('$search_phrase')
		OR MATCH(t2.description) AGAINST ('$search_phrase')
		OR MATCH(t3.description) AGAINST ('$search_phrase')
		OR t1.function_name = '$search_phrase'
 	ORDER BY match_score DESC";
	$result_functions = $mysqli->query($sql)
	or die ($mysqli->error);
}
//-----------------------------------Parsing for desintatoin language specifics ends--------------------------------


//---------------------------------------------Parsing for possible questions-----------------------------
//To be implemented soon
/*if (!$language) {
	if (is_numeric($user_id)) {
		$sql = "SELECT t1.question_id, t1.title, t1.body, t1.author_id, t1.date_posted, t1.upvotes, t1.downvotes, t1.src_language, t1.des_language, t1.tagged_language, MATCH(t1.title, t1.body) AGAINST ('$search_phrase') as `match_score`, t3.status, t2.username
		FROM questions as t1 
		LEFT JOIN users as t2 
		ON t2.user_id = t1.author_id
		LEFT JOIN question_feedback as t3 
		ON t3.question_id = t1.question_id
		AND t3.user_id = '$user_id'
		WHERE MATCH(t1.title, t1.body) AGAINST ('$search_phrase')
		ORDER BY match_score DESC";
	}
	else {
		$sql = "SELECT t1.question_id, t1.title, t1.body, t1.author_id, t1.date_posted, t1.upvotes, t1.downvotes, t1.src_language, t1.des_language, t1.tagged_language, MATCH(t1.title, t1.body) AGAINST ('$search_phrase') as `match_score`, null as `status`, t2.username
		FROM questions as t1 
		LEFT JOIN users as t2 
		ON t2.user_id = t1.user_id
		WHERE MATCH(t1.title, t1.body) AGAINST ('$search_phrase')
		ORDER BY match_score DESC";
	}
}
else {
		$sql = "SELECT t1.question_id, t1.title, t1.body, t1.author_id, t1.date_posted, t1.upvotes, t1.downvotes, t1.src_language, t1.des_language, t1.tagged_language, MATCH(t1.title, t1.body) AGAINST ('$search_phrase') as `match_score`, t3.status, t2.username
		FROM questions as t1 
		LEFT JOIN users as t2 
		ON t2.user_id = t1.author_id
		LEFT JOIN question_feedback as t3 
		ON t3.question_id = t1.question_id
		AND t3.user_id = '$user_id'
		WHERE MATCH(t1.title, t1.body) AGAINST ('$search_phrase') AND (
			t1.tagged_language = '$language' OR t1.des_language = '$language'
		)
		ORDER BY match_score DESC";
}

$result_questions = $mysqli->query($sql)
or die ($mysqli->error);
$search_results = array();
while ($row = mysqli_fetch_array($result_questions, MYSQLI_ASSOC)) {
	$row['author'] = new User(array(
		'user_id' => $row['author_id'],
		'username' => $row['username'])
	);
	$question = new Question($row);
	$search_results[] = $question;
}*/

//-----------------------------------Load search results--------------------------------------------------------
/*if ($language) {
	$sql = "
	SELECT t1.description, t1.function_name, t1.function_id, MATCH(t1.description) AGAINST ('$search_phrase') as `tbl1_score`, MATCH(t2.description) AGAINST ('$search_phrase') as `tbl2_score`, MATCH(t3.description) AGAINST ('$search_phrase') as `tbl3_score`, MATCH(t1.function_name) AGAINST ('$search_phrase') as `tbl4_score`, MATCH(t1.function_name) AGAINST ('$search_phrase') *3 + (MATCH(t1.description) AGAINST ('$search_phrase') * 2 + MATCH(t2.description) AGAINST ('$search_phrase') *1.5 + MATCH(t3.description) AGAINST ('$search_phrase')) * 0.5 as `match_score`, t4.language_name
	FROM functions as t1
	LEFT JOIN category as t2
	ON t2.category_id = t1.category_id
	LEFT JOIN super_category as t3
	ON t3.super_id = t2.type
	LEFT JOIN languages as t4 
	ON t4.language_id = t1.language
	WHERE t1.language = '$language' AND (
		MATCH(t1.function_name) AGAINST ('$search_phrase')
		OR MATCH(t1.description) AGAINST ('$search_phrase')
		OR MATCH(t2.description) AGAINST ('$seach_phrase')
		OR MATCH(t3.description) AGAINST ('$search_phrase')
	)
 	ORDER BY match_score DESC";
	$result_functions = $mysqli->query($sql)
	or die ($mysqli->error);*/

/*	$sql = "SELECT t1.post_text, t1.post_title, t1.post_id, t1.author_id as `user_id`, DATE_FORMAT(t1.date_posted, '%M %e, %Y') as `date_posted`, t2.username as `username`, MATCH(t1.post_text, t1.post_title) AGAINST ('$search_phrase') as `match_score`
	FROM posts as t1
	LEFT JOIN users as t2
	ON t2.user_id = t1.author_id
	WHERE MATCH(t1.post_text, t1.post_title) AGAINST ('$search_phrase')
	AND t1.tagged_language = '$language'
	ORDER BY match_score DESC";
	$result_posts = $mysqli->query($sql)
	or die ($mysqli->error);*/
/*	$sql = "SELECT t1.post_text, t1.post_title, t1.post_id, t1.author_id as `user_id`, DATE_FORMAT(t1.date_posted, '%M %e, %Y') as `date_posted`, t2.username as `username`, MATCH(t1.post_text, t1.post_title) AGAINST ('$search_phrase') * 1.5 as `match_score`
	FROM posts as t1
	LEFT JOIN users as t2
	ON t2.user_id = t1.author_id
	WHERE MATCH(t1.post_text, t1.post_title) AGAINST ('$search_phrase')
	ORDER BY match_score DESC";
	$result_posts = $mysqli->query($sql)
	or die ($mysqli->error);*/
 //The array that will hold the search results
/*while ($row = mysqli_fetch_array($result_posts, MYSQLI_ASSOC)) {
	$post = new Post($row);
	$post->author = new User($row);
	$search_results[] = $post;
}*/
$search_results = array();
while ($row = mysqli_fetch_array($result_functions, MYSQLI_ASSOC)) {
	$function = new LanguageFunction($row);
	$function->function_language = new Language($row);
	$search_results[] = $function;
}
//print_r($search_results);
usort($search_results, function($a, $b) {
	if ($a->match_score == $b->match_score) {
		return 0;
	}
	else {
		return ($a->match_score > $b->match_score) ? 1 : -1;
	}
});
$search_results = array_reverse($search_results);
//print_r($search_results);
//--------------------------------------------Parsing for search results end----------------------------------
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete-alt.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">
		<link rel="stylesheet" type="text/css" href="/css/search.css">
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">
		<link rel="stylesheet" href="css/highlightjs/tomorrow.css">
	</head>
	<body ng-controller="main" class="container-fluid" ng-init="init('<?php echo $original_search_phrase;?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="row">
					<div class="col-md-12">
						<a href ng-click="questionSearch()" style="text-align;right;display:block;float:right;position:relative;">[?]</a>
					</div>
					<div class="col-md-9">
						<angucomplete-alt
							id="members"
			              placeholder="Search functions/descriptions"
			              pause="400"
			              selected-object="searchPhrase"
			              remote-url="http://paraphrase.io/postRequests/addFunction/searchFunction.php?function="
			              title-field="title"
			              description-field="description"
			              input-class="form-control form-control-small"
			              input-changed="updateScope">
					</div>
			        <div class="col-md-2">
						<select ng-model="selectedLanguageID" class="form-control" ng-options="language.language_id as language.language_name for language in languages">
						</select>
			        </div>
			        <div class="col-md-1">
			        	<button class="btn btn-primary" ng-click="attemptTranslation()" style="float:right;display:block;">Search</button>
			        </div>
			        <div class="col-md-12">
						<p class="text-left">
							<small style="display:inline-block;text-align:left;">
								<?php
									if (isset($translation) && $translation) {
										?>
										<small>
											Displaying <?php 
												echo count($search_results) . 'search results';
											?>
										</small>
										<?php
									}
									else {
										?>
										<small>
											Displaying <?php 
												echo count($search_results) . ' search results';
											?>
										</small>
										<?php
									}
								?>
							</small>
						</p>
					</div>
				</div>
				<div class="row">
					<?php
					if (count($search_results) > 0) {
						foreach ($search_results as $key=>$object) {
							if (get_class($object) == 'Post') {
								//Analyze for post
								?>
								<div class="col-md-12">
									<div class="row">
										<div class="col-md-1">
											<img src="/ui/post_icon.png" style="padding-top:15px;height:50px;width:auto;">
										</div>
										<div class="col-md-11">
											<div class="resultBox">
												<h3 style="margin-bottom:5px;"><a href="post.php?id=<?php echo $object->post_id;?>"><?php echo $object->post_title;?></a></h3>
												<h5>By: <a href="profile.php?id=<?php echo $object->author->user_id;?>"><?php echo $object->author->username;?></a> | Date Posted: <?php echo $object->date_posted;?></h5>
												<p>
													<?php echo substr($object->post_text, 0, 500);?>....<a href="post.php?id=<?php echo $object->post_id;?>">Read More</a>
												</p>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							elseif (get_class($object) == 'Question') {
								?>
								<div class="col-md-12">
									<div class="row">
										<div class="col-md-1">
											<img src="/ui/post_icon.png" style="padding-top:15px;height:50px;width:auto;">
										</div>
										<div class="col-md-11">
											<div class="resultBox">
												<h3 style="margin-bottom:5px;"><a href="question.php?id=<?php echo $object->question_id;?>"><?php echo $object->title;?></a></h3>
												<h5>By: <a href="profile.php?id=<?php echo $object->author->user_id;?>"><?php echo $object->author->username;?></a> | Posted <?php echo $object->date_posted_ago;?> ago</h5>
												<p>
													<?php echo substr($object->body, 0, 500);?>....<a href="question.php?id=<?php echo $object->question_id;?>">Read More</a>
												</p>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							elseif (get_class($object) == 'LanguageFunction')  {
								//Analyze for function
								?>
								<div class="col-md-12">
									<div class="row">
										<div class="col-md-1">
											<img src="/ui/builtin_icon.jpg" style="padding-top:15px;height:50px;width:auto;">
										</div>
										<div class="col-md-11 resultBox">
											<h3><a href="function.php?id=<?php echo 	$object->function_id;?>"><?php echo $object->function_language->language_name . "'s ";?><?php echo $object->function_name;?></a>
											</h3>

											<p>
												<?php echo $object->description;?>
											</p>
										</div>
									</div>
								</div>
								<?php
							}
						}
					}
					else {
						?>
						<div class="col-md-12">
							<?php
							if ($translation) {
								?>
								<strong>No other search results found for <?php echo $search_phrase;?> in <?php echo $to_language_name;?></strong>
								<?php
							}
							else {
								?>
								<strong>No search results found for <?php echo $search_phrase;?> in <?php echo $to_language_name;?></strong>
								<?php
							}
							?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</body>
</html>
<script src="/bower_components/angular/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/highlightjs/highlight.pack.js"></script>
<script src="dependencies/angular-highlight/angular-highlightjs.min.js"></script>
<script src="/bower_components/angucomplete-alt/dist/angucomplete-alt.min.js"></script>
<script src="dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="/angular/search/search.js"></script>