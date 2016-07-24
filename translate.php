<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation') || ($class_name == 'TranslateFactory')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}	
}
try {
	//Check the type contracts for the get variables
	if (is_numeric($_GET['ffi']) && is_numeric($_GET['lan'])) {
		$ffi = $_GET['ffi'];
		$lan = $_GET['lan'];

		$current_supported_languages = array(1, 2, 4, 5);
		if (!in_array($lan, $current_supported_languages)) {
			header('Location: http://paraphrase.io/404.php');
		}

	/*}
	if (!empty($_GET['src']) && is_numeric($_GET['lan1']) && is_numeric($_GET['lan2'])) {*/
		//Now depreciated, search by function id instead of ambigious function name since name collisions
	/*	require_once __DIR__ . '/includes/Database.php';
		require_once __DIR__ . '/includes/Language.php';
		require_once __DIR__ . '/includes/LanguageFunction.php';
		require_once __DIR__ . '/includes/User.php';
		$src = trim(Database::sanitize($_GET['src']), '.');

		$src1 = $src . '()';

		$lan1 = $_GET['lan1'];
		$lan2 = $_GET['lan2'];

		$sql = "SELECT function_id, category_id, description, syntax FROM functions WHERE function_name = '$src' OR function_name = '$src1'";

		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$row = mysqli_fetch_row($result);
		$fn_id = $row[0];
		$cat_id = $row[1];
		$description = $row[2];
		$syntax = $row[3];

		//Parse for first language
		$sql = "SELECT language_name 
		FROM languages
		WHERE language_id = '$lan1'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);	
		if (mysqli_num_rows($result) == 1) {
			$row = mysqli_fetch_row($result);
			$language1 = new Language(array(
				'language_id' => $lan1,
				'language_name' => $row[0])
			);
		}
		//Parse for second language
		$sql = "SELECT language_name 
		FROM languages
		WHERE language_id = '$lan2'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		if (mysqli_num_rows($result) == 1) {
			$row = mysqli_fetch_row($result);
			$language2 = new Language(array(
				'language_id' => $lan2,
				'language_name' => $row[0])
			);
		}

		//Get the translation id, have to think about how to handle if translation doesnt exist
		$sql = "SELECT translation_id FROM translations WHERE from_function_id = '$fn_id' AND to_language_id = '$lan2'";
		$result = $mysqli->query($sql)
		or die ($mysqli->error);
		$translation_id = mysqli_fetch_row($result)[0];
	*/

		$mysqli = Database::connection(); //Create the connection variable
		$user_id = User::get_current_user_id();

		$sql_load_from_function = "SELECT t1.function_id, t1.category_id, t1.description, t1.syntax, t1.big_o_average, t1.big_o_average_summary, t1.big_o_worst_case, t1.big_o_worst_case_notes, t1.big_o_best_case_notes, t1.function_name, t2.language_id, t2.language_name, t1.description
		FROM functions as t1 
		LEFT JOIN languages as t2 
		ON t2.language_id = t1.language
		WHERE t1.function_id = '$ffi'";
		$result_load_from_function = $mysqli->query($sql_load_from_function)
		or die ($mysqli->error);

		$sql_load_to_language = "SELECT language_id, language_name FROM languages WHERE language_id = '$lan'";
		$result_load_to_language = $mysqli->query($sql_load_to_language)
		or die ($mysqli->error);

		if (($result_load_from_function->num_rows == 1) && ($result_load_to_language->num_rows == 1)) {
			//The request is valid the function and language both exist, create the objects
			
			//Parse for the from function and language first
			$row = mysqli_fetch_array($result_load_from_function, MYSQLI_ASSOC); 
			$from_language = new Language(array(
				'language_id' => $row['language_id'],
				'language_name' => $row['language_name'])
			);
			$row['function_language'] = $from_language;
			$from_function = new LanguageFunction($row);

			//Now create the to language
			$row = mysqli_fetch_array($result_load_to_language, MYSQLI_ASSOC);
			$to_language = new Language($row);

			if ($to_language->language_id == $from_language->language_id) {
				throw new OutOfRangeException('OutOfRangeException');
			}
			else {

				//Finally check for the existence of this translation link, and if not, create it
				$translation = new IncomingTranslation(array(
					'from_function' => $from_function,
					'to_language' => $to_language)
				);
				$translation_id = TranslateFactory::existence($translation); //This static method handles everything
			}
		}
		else {
			throw new OutOfRangeException('OutOfRangeException occured on request');
		}
	}
	else {
		throw new OutOfRangeException;
	}
}
catch (OutOfRangeException $e) {
	header('Location: http://paraphrase.io/404.php');
}
?>
<?php
require_once 'navbar.php';
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete-alt.css">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog-theme-default.css">
		<link rel="stylesheet" href="css/highlightjs/tomorrow.css">
		<link rel="stylesheet" type="text/css" href="css/translate.css">
		<link rel="stylesheet" type="text/css" href="dependencies/angular-charts/angular-chart.css">
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="init('<?php echo $translation_id;?>', '<?php echo $from_function->function_id;?>', '<?php echo $from_function->category_id;?>', '<?php echo $from_function->function_language->language_id;?>', '<?php echo $to_language->language_id;?>', '<?php echo $user_id;?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="row">
					<div class="col-md-9" style="padding-left:0px;margin-left:0px;">
						<angucomplete-alt	
							id="members"
			              placeholder="Search functions/descriptions"
			              pause="400"
			              selected-object="searchPhrase"
			              remote-url="http://paraphrase.io/postRequests/addFunction/searchFunction.php?function="
			              title-field="title"
			              description-field="description"
			              input-class="form-control form-control-small"
			              input-changed="updateScope" />
			        </div>
			        <div class="col-md-2">
						<select ng-model="selectedLanguageID" class="form-control" ng-options="language.language_id as language.language_name for language in languages">
						</select>
			        </div>
			        <div class="col-md-1">
			        	<button class="btn btn-primary" ng-click="attemptTranslation()" style="float:right;display:block;">Search</button>
			        </div>
			        <div class="col-md-7" style="padding-left:0px;margin-left:0px;">
						<small>Try searching a function like: "strtoupper()" or a description "Creating an array from an array-like object"</small>
			        </div>
			        <div class="col-md-5">
			        	<p class="text-right">
			        		<small style="text-align:right;display:block;">Supported languages: PHP, Javascript, C, Python, Ruby, Swift, Java, C++, C#</small>
			        	</p>
			        </div>
			    <div>
			    <div class="row">
				   	<div class="col-md-12">
						<h2 style="margin-bottom:3px;padding-bottom:0px;">
							<?php echo $from_function->function_language->language_name . "'s $from_function->function_name equivalent in $to_language->language_name";?>
						</h2>
						<hr style="margin-top:0px;padding-top:0px;margin-bottom:3px;padding-bottom:0px;">
						First asked: {{firstAsked}}, 
						<span ng-show="upvotes > 0">
							<span ng-show="upvoted">You 
								<span ng-show="upvotes > 1">and {{upvotes - 1}} others have upvoted this</span>
								<span ng-show="upvotes == 1">have upvoted this</span>
							</span>
							<span ng-show="!upvoted">
								{{upvotes}} people have upvoted this
							</span>
						</span>
					</div>
					<div class="col-md-4 col-md-push-8" style="min-height:500px;">
						<p class="text-center">
							<div class="translateStatContainer">
								<h3>Answers:</h3><h4>{{answers}}</h4>
							</div>
							<div class="translateStatContainer">
								<h3>Views:</h3><h4>{{views}}</h4>
							</div>
							<canvas id="line" class="chart chart-line" data="data.dataset"
								labels="data.labels" legend="true" series="series" height="100"
								click="onClick">
							</canvas>
							<div style="margin-bottom:10px;">
								<h3 style="display:inline-block;">Contributors</h3>
								<span style="float:right;">
									<strong>
										{{contributors.length}}
									</strong>
								</span>
							</div>
							<div class="row contributorBox" ng-repeat="contributor in contributors">
<!-- 									<div>
										<h5 style="text-align:center;display:inline-block;">
											{{contributor.upvotes}}
										</h5>
										<small>
											upvotes
										</small>
									</div>
									<h5 style="text-align:center;display:inline-block;">
										{{contributor.downvotes}}
									</h5>
									<small>
										downvotes
									</small> -->
<!-- 								<div class="col-md-2 col-md-push-2 col-md-pull-6" ng-show="translations.unshift.author.user_id == contributor.user_id">
									<img src="https://cdn2.iconfinder.com/data/icons/krispicons-modern-flat-icons-freebie/111/11.png" width="30" height="30">
								</div> -->
								<div class="col-md-10 col-md-offset-2">
										<a ng-href="profile.php?id={{contributor.user_id}}">
											<img class="img-responsive pull-left" ng-src="{{contributor.avatar}}" width="85" height="85" style="margin-right:5px;">
										</a>
										<a ng-href="profile.php?id={{contributor.user_id}}" class="pull-right">
											<h5 style="padding-top:0px;margin-top:0px;margin-bottom:1px;padding-bottom:0px;">
												<a ng-href="profile.php?id={{contributor.user_id}}">
													{{contributor.username}}
												</a>
											</h5>
											{{contributor.points}} reputation
											<br/>
											Answered on {{contributor.date_posted}}
										</a>
									</div>
							</div>
						</p>
					</div>
					<div class="col-md-8 col-md-pull-4">
						<p>
							<h3>
								<a href="function.php?id=<?php echo $from_function->function_id;?>">
									<?php echo $from_function->function_language->language_name?>
								</a>
							</h3>
							<h6>Translation Request</h6>
							<a href="function.php?id=<?php echo $from_function->function_id;?>">
								<div hljs source="'<?php echo $from_function->function_name;?>'" language="<?php echo strtolower($from_function->function_language->language_name);?>">
								</div>
							</a>
							<strong>Summary</strong>
							<p>
								<?php echo $from_function->description;?>
							</p>
							<strong>Syntax</strong>
							<blockquote>
								<div hljs source="'<?php echo $from_function->syntax;?>'">
								</div>
							</blockquote>
						</p>
					</div>
<!-- 					<div class="col-md-8 col-md-pull-4">
						<hr>
					</div> -->
					<div class="col-md-8 col-md-pull-4">
<!-- 						<h3 style="display:inline;margin-bottom:0px;padding:0px;"><?php echo $to_language->language_name?></h3>
						<br/>
						<small>Translation answers</small> -->
						<tabset>
							<div class="navbar-left">
								<h3 style="display:inline;margin-bottom:0px;padding:0px;"><?php echo $to_language->language_name?></h3>
							</div>
							<tab heading="Newest" class="navbar-right" active="false" select="changeTab('Newest')">
								<div class="answers">
									<div ng-repeat="translation in data.translations">
										<div ng-show="translation.note">
											<form ng-show="translation.showEdit">
												<div class="form-group">
													<p class="help-block" ng-show="data.postType == 2">
														<small>
														A standalone note/annotation that includes relevant and helpful information to the current translation or other related material. Markdown is supported, you can view its syntax <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet">here</a>
														</small>
													</p>
													<textarea ng-model="translation.edittedSummary" class="form-control" ng-show="translation.showEdit" rows="5"></textarea>
												</div>
												<div class="form-group" ng-show="translation.edittedSummary">
													<p class="help-text">
														<strong>
														Preview
														</strong>
													</p>
													<div marked="translation.edittedSummary">
													</div>
												</div>
												<input type="submit" ng-click="editTranslation(translation.result_id)" class="btn btn-primary" value="Submit Changes">
											</form>		
											<p>
												<!-- Note type -->
												<p ng-show="!translation.showEdit">
													{{translation.comment}}
												</p>
												<div class="row">
													<div class="col-md-12">
														<hr style="padding:left:3px;padding-right:3px;margin-bottom:3px;padding-bottom:0px;padding-top:0px;margin-top:3px;">
													</div>
													<div class="col-md-12">
														<div class="row" style="margin-bottom:5px;">
															<div class="col-md-8">
																<div class="row">
																	<div class="col-md-12">
																		<small ng-show="translation.upvoted">
																			You <span ng-show="translations[0].upvotes > 1"> and {{translation.upvotes - 1}} other(s) upvoted </span><span ng-show="translations[0].upvotes == 1"> upvoted this
																		</small>
																		<small ng-show="(translation.upvotes > 0) && !translation.upvoted">
																			{{translation.upvotes}} people upvoted
																		</small>
																		<span ng-show="(translation.upvotes > 0) && (translation.downvotes > 0)" class="glyphicon glyphicon-asterisk" aria-hidden="true" style="font-size:0.5em;top:50%;"></span>
																		<small ng-show="translation.downvoted">
																			You <span ng-show="translation.downvotes > 1"> and {{translation.downvotes - 1}} other(s) downvoted </span><span ng-show="translation.downvotes == 1"> downvoted this</span>
																		</small>
																		<small ng-show="(translation.downvotes > 0) && !translation.downvoted">
																			{{translation.downvotes}} people downvoted
																		</small>
																	</div>
																	<div class="col-md-12 options">
																		<a href ng-click="translation.showEdit = false" ng-show="translation.showEdit">Cancel Edit</a>
																		<a href ng-click="handleEdit(translation.result_id)" ng-show="!translation.showEdit && userID == translation.author.user_id">Edit
																		</a>
																		<a href ng-click="deleteTranslation(translation.result_id); translation.upvoted = !translation.upvoted; translation.downvoted = (translation.upvoted) ? translated.downvoted : !translation.downvoted;" ng-show="translation.author.user_id == userID">Delete</a>
																		<a href ng-click="upvote(translation.result_id)" ng-style="translation.upvoteStyle">
																			<span class="glyphicon glyphicon-thumbs-up" style="font-size:1em;"></span>
																			Upvote
																		</a>
																		<a href ng-click="downvote(translation.result_id)" ng-style="translation.downvoteStyle">
																			<span class="glyphicon glyphicon-thumbs-down" style="font-size:1em;"></span>
																			Downvote
																		</a>
																		<a href ng-click="showCommentBox = true" ng-show="!showCommentBox">Comment</a>
																	</div>
																</div>
															</div>
															<div class="col-md-4">
																<div class="authorBox">
																	Answered on {{translation.date_posted}}
																	<br/>
																	<img ng-src="{{translation.author.avatar}}" style="width:auto;height:50px;">
																	<a ng-href="profile.php?id={{translation.author.user_id}}">
																		{{translation.author.username}}
																		<br/>
																		{{translation.author.points}} reputation
																		<br/>
																	</a>
																</div>
															</div>
														</div>
													</div>
	<!-- 												<div class="col-md-12">
														<hr>
													</div> -->
													<div class="col-md-12">
														<div ng-show="showCommentBox">
															<textarea ng-model="data.commentBox" class="form-control"></textarea>
															<button ng-click="postTranslationComment(translation.result_id)" class="btn btn-primary btn-xs">Submit</button>
															<a href ng-click="showCommentBox = false" ng-show="showCommentBox">Cancel</a>
														</div>
													</div>
												</div>
											</p>
											<div class="translationComment" ng-repeat="comment in translation.comments | limitTo:data.x_top_comments">
												<div class="row">
													<div class="col-md-12">
														<a ng-href="profile.php?id={{comment.author.user_id}}" style="float:left;">{{comment.author.username}}</a>
														<span style="float:right;"><span ng-show="comment.author.user_id == userID"><a href ng-click="deleteComment(comment.comment_id)">Delete</a> | </span><small>Posted {{comment.date_posted}}</small></span>
													</div>
													<div class="col-md-12">
														<hr>
													</div>
													<div class="col-md-12">
														<p ng-bind-html="comment.comment_text">
														</p>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<p class="text-center" ng-show="data.x_top_comments < translations[0].comments.length">
														<a href ng-click="incrementTopComments()">Load More Comments</a>
													</p>
												</div>
											</div>
										</div>
										<div ng-show="!translation.note && translation.default_show">
											<form ng-show="translation.showEdit == 2">
												<p class="help-block">
													An existing function in the desintation language sufficient as an equivalent to <?php echo $from_function->function_name;?>
												</p>
												<angucomplete-alt	
													id="members"
									              placeholder="Search functions/methods in <?php echo $to_language->language_name;?>"
									              pause="400"
									              selected-object="translation.edittedLinkedFunction"
									              remote-url="http://paraphrase.io/postRequests/info/searchFunctionInLanguage.php?lan=<?php echo $to_language->language_id;?>&function="
									              title-field="title"
									              description-field="description"
									              input-class="form-control form-control-small"/>
									             </angucomplete-alt>
									            <input type="submit" ng-click="editTranslation(translation.result_id)" value="Submit Changes" class="btn btn-primary btn-sm">
											</form>
											<form ng-show="translation.showEdit == 3">
												<div class="form-group">
													<label for="translation"><strong>Translation</strong></label>
													<p class="help-block">Your primary "translation"/answer to be attached to your post
													</p>
													<textarea ng-model="translation.edittedTranslation" class="form-control" ng-show="translation.showEdit" rows="5"></textarea>
												</div>
												<div class="form-group">
													<label for="comments" ng-show="data.postType == 1"><strong>Summary</strong></label>
													<p class="help-block">Any additional comments/explanations/notes/details you want to add, possibly elaborating the answer or alternative answers. Use 'code' tag to specify code</p>
													<textarea rows="10" class="form-control" ng-model="translation.edittedSummary" name="comment"></textarea>
												</div>
												<div class="form-group" ng-show="translation.edittedSummary">
													<p class="help-block">
														Preview
													</p>
													<div marked="translation.edittedSummary">
													</div>
												</div>
												<input type="submit" ng-click="editTranslation(translation.result_id)" class="btn btn-primary" value="Submit Changes">
											</form>											
											<a ng-href="function.php?id={{translation.suggested_function.function_id}}" ng-if="translation.single">
												<div hljs source="translation.suggested_function.function_name" language="<?php echo strtolower($language2->language_name);?>" ng-show="!translation.showEdit">
												</div>
											</a>
											<div hljs source="translation.suggested_function.function_name" language="<?php echo strtolower($language2->language_name);?>" ng-if="!translation.single" ng-show="!translation.showEdit">
											</div>
											<strong ng-show="!translation.showEdit">Summary</strong>
											<p ng-show="translation.single && !translation.showEdit" ng-bind-html="translation.suggested_function.description">
												<a ng-href="function.php?id={{translation.suggested_function.function_id}}">
													<?php echo $to_language->language_name . "'s built in {{translation.suggested_function.function_name}} <br/>";
													?>
												</a>
										<!-- 		{{translation.suggested_function.description}} -->
											</p>
											<p ng-show="!translation.single && !translation.showEdit">
												<div marked="translation.comment" ng-show="!translation.showEdit">
												</div>
										<!-- 		{{translation.comment}} -->
											</p>
											<span ng-show="translation.single && !translation.showEdit"><strong>Syntax</strong></span>
											<blockquote ng-show="translation.single && !translation.showEdit">
												<div hljs source="translation.suggested_function.syntax" language="<?php echo strtolower($language2->language_name);?>" id="no">
												</div>
											</blockquote>
											<div class="row" style="margin-bottom:10px;">
												<div class="col-md-12">
													<hr style="padding:left:3px;padding-right:3px;margin-bottom:3px;padding-bottom:0px;padding-top:0px;margin-top:3px;opacity:0.5">
												</div>
												<div class="col-md-12">
													<div class="row" style="margin-bottom:5px;">
														<div class="col-md-8">
															<div class="row">
																<div class="col-md-12">
																	<small ng-show="translation.upvoted">
																		You <span ng-show="translations[0].upvotes > 1"> and {{translation.upvotes - 1}} other(s) upvoted </span><span ng-show="translations[0].upvotes == 1"> upvoted this
																	</small>
																	<small ng-show="(translation.upvotes > 0) && !translation.upvoted">
																		{{translation.upvotes}} people upvoted
																	</small>
																	<span ng-show="(translation.upvotes > 0) && (translation.downvotes > 0)" class="glyphicon glyphicon-asterisk" aria-hidden="true" style="font-size:0.5em;top:50%;"></span>
																	<small ng-show="translation.downvoted">
																		You <span ng-show="translation.downvotes > 1"> and {{translation.downvotes - 1}} other(s) downvoted </span><span ng-show="translation.downvotes == 1"> downvoted this</span>
																	</small>
																	<small ng-show="(translation.downvotes > 0) && !translation.downvoted">
																		{{translation.downvotes}} people downvoted
																	</small>
																</div>
																<div class="col-md-12 options">
																	<a href ng-click="handleEdit(translation.result_id)" ng-show="!translation.showEdit && userID == translation.author.user_id">Edit</a>
																	<a href ng-click="translation.showEdit = 0" ng-show="translation.showEdit">Cancel Edit</a>
																	<a href ng-click="deleteTranslation(translation.result_id); translation.upvoted = !translation.upvoted; translation.downvoted = (translation.upvoted) ? translated.downvoted : !translation.downvoted;" ng-show="translation.author.user_id == userID">Delete</a>
																	<a href ng-click="upvote(translation.result_id)" ng-style="translation.upvoteStyle">
																		<span class="glyphicon glyphicon-thumbs-up" style="font-size:1em;"></span>
																		Upvote
																	</a>
																	<a href ng-click="downvote(translation.result_id)" ng-style="translation.downvoteStyle">
																		<span class="glyphicon glyphicon-thumbs-down" style="font-size:1em;"></span>
																		Downvote
																	</a>
																	<a href ng-click="showCommentBox = true" ng-show="!showCommentBox">Comment</a>
																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="authorBox">
																Answered on {{translation.date_posted}}
																<br/>
																<img ng-src="{{translation.author.avatar}}" style="width:auto;height:50px;">
																<a ng-href="profile.php?id={{translation.author.user_id}}">
																	{{translation.author.username}}
																	<br/>
																	{{translation.author.points}} reputation
																	<br/>
																</a>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="comments" style="margin-top:20px;">
												<div class="translationComment" ng-repeat="comment in translation.comments | limitTo:data.x_top_comments">
													<div class="row">
														<div class="col-md-12">
															<a ng-href="profile.php?id={{comment.author.user_id}}" style="float:left;">{{comment.author.username}}</a>
															<span style="float:right;"><span ng-show="comment.author.user_id == userID"><a href ng-click="deleteComment(comment.comment_id)">Delete</a> | </span><small>Posted {{comment.date_posted}}</small></span>
														</div>
														<div class="col-md-12">
															<hr style="opacity: 0.8">
														</div>
														<div class="col-md-12">
															<p ng-bind-html="comment.comment_text">
															</p>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<p class="text-center" ng-show="data.x_top_comments < translations[0].comments.length">
															<a href ng-click="incrementTopComments()">Load More Comments</a>
														</p>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div ng-show="showCommentBox">
													<textarea ng-model="data.commentBox" class="form-control"></textarea>
													<button ng-click="postTranslationComment(translation.result_id)" class="btn btn-primary btn-xs">Submit</button>
													<a href ng-click="showCommentBox = false" ng-show="showCommentBox">Cancel</a>
												</div>
											</div>
										</div>
										<div ng-show="!translation.default_show" style="margin-bottom:10px;">
											<div class="row">
												<div class="col-md-8">
													<a href ng-click="showHiddenAnswer(translation.result_id)">
														<i>
															Answer hidden due to high downvotes, click to show.
														</i>
													</a>
												</div>
												<div class="col-md-4">
													<div class="authorBox">
														Answered on {{translation.date_posted}}
														<br/>
														<img ng-src="{{translation.author.avatar}}" style="width:auto;height:50px;">
														<a ng-href="profile.php?id={{translation.author.user_id}}">
															{{translation.author.username}}
															<br/>
															{{translation.author.points}} reputation
															<br/>
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<hr ng-show="!$last" style="opacity:1;margin-bottom:30px;">
											</div>
										</div>
									</div>
								</div>
								<p class="text-center" ng-show="translations.length == 0 && !submitted">
									<strong>
										No answers or notes found. Be the first to submit one below!
									</strong>
								</p>
<!-- 								<div ng-show="translations.length == 0">
									<form ng-show="!submitted">
										<p class="text-center">
											<strong>
												No answers or notes found. Be the first to submit one below!
											</strong>
										</p>
										<div class="form-group">
											<label class="col-md-2 control-label" style="padding-left:0px;"><strong>Post Type</strong></label>
											<div class="col-md-10">
												<div style="float:right;">
													<label class="radio-inline">
														<input type="radio" ng-model="data.postType" value="1">Translation
													</label>				
													<label class="radio-inline">
														<input type="radio" ng-model="data.postType" value="2">Note
													</label>
												</div>
											</div>
											<br/>
											<p class="help-block" ng-show="data.postType == 1">
												An additional (or revised) translation along with a recommended but optional explanation/summary
											</p>
											<p class="help-block" ng-show="data.postType == 2">
												A standalone note/annotation that includes relevant and helpful information to the current translation or other related material. 
											</p>
										</div>
										<div class="form-group" ng-show="data.postType == 1">
											<label for="translation"><strong>Translation</strong></label>
											<p class="help-block">Your translation to be attached to your post
												<label class="checkbox-inline" style="float:right;">
													<input type="checkbox" ng-model="data.single" ng-true-value="true" ng-false-value="false">Single, existing function <a href ng-click="singleExpand()">[?]</a>
												</label>
											</p>
											<angucomplete-alt	
												id="members"
								              placeholder="Search functions/methods in <?php//echo $to_language->language_name;?>"
								              pause="400"
								              selected-object="data.linked_function"
								              remote-url="http://paraphrase.io/postRequests/info/searchFunctionInLanguage.php?lan=<?php //echo $to_language->language_id;?>&function="
								              title-field="title"
								              description-field="description"
								              input-class="form-control form-control-small"
								              ng-show="data.single"/>
								             </angucomplete-alt>
											<textarea rows="5" class="form-control" ng-model="data.translation" ng-show="!data.single"></textarea>
										</div>
										<div class="form-group">
											<label for="comments" ng-show="data.postType == 1"><strong>Summary</strong></label>
											<label for="comment" ng-show="data.postType == 2"><strong>Note</strong></label>
											<p class="help-block">Any additional comments/explanations/notes you want to add. Use 'code' tag to specify code</p>
											<textarea rows="10" class="form-control" ng-model="data.comment" name="comment"></textarea>
										</div>
										<div class="form-group" ng-show="data.postType == 1 && data.comment">
											<p class="help-text">
												<strong>
													Preview
												</strong>
											</p>
											<div marked="data.comment">
											</div>
										</div>
										<input type="submit" ng-click="submitNoteTranslation()" class="btn btn-default">
									</form>
								</div> -->
							</tab>
							<tab heading="Votes" class="navbar-right" active="true" select="changeTab('Votes')">
								<div class="answers">
									<div ng-repeat="translation in data.translations">
										<div ng-show="translation.note">
											<form ng-show="translation.showEdit">
												<div class="form-group">
													<p class="help-block" ng-show="data.postType == 2">
														A standalone note/annotation that includes relevant and helpful information to the current translation or other related material. 
													</p>
													<textarea ng-model="translation.edittedSummary" class="form-control" ng-show="translation.showEdit" rows="5"></textarea>
												</div>
												<div class="form-group" ng-show="translation.edittedSummary">
													<p class="help-text">
														<strong>
															Preview
														</strong>
													</p>
													<div marked="translation.edittedSummary">
													</div>
												</div>
												<input type="submit" ng-click="editTranslation(translation.result_id)" class="btn btn-primary" value="Submit Changes">
											</form>		
											<p>
												<!-- Note type -->
												<div ng-show="!translation.showEdit">
													<div marked="translation.comment">
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<hr style="padding:left:3px;padding-right:3px;margin-bottom:3px;padding-bottom:0px;padding-top:0px;margin-top:3px;">
													</div>
													<div class="col-md-12">
														<div class="row" style="margin-bottom:5px;">
															<div class="col-md-8">
																<div class="row">
																	<div class="col-md-12">
																		<small ng-show="translation.upvoted">
																			You <span ng-show="translations[0].upvotes > 1"> and {{translation.upvotes - 1}} other(s) upvoted </span><span ng-show="translations[0].upvotes == 1"> upvoted this
																		</small>
																		<small ng-show="(translation.upvotes > 0) && !translation.upvoted">
																			{{translation.upvotes}} people upvoted
																		</small>
																		<span ng-show="(translation.upvotes > 0) && (translation.downvotes > 0)" class="glyphicon glyphicon-asterisk" aria-hidden="true" style="font-size:0.5em;top:50%;"></span>
																		<small ng-show="translation.downvoted">
																			You <span ng-show="translation.downvotes > 1"> and {{translation.downvotes - 1}} other(s) downvoted </span><span ng-show="translation.downvotes == 1"> downvoted this</span>
																		</small>
																		<small ng-show="(translation.downvotes > 0) && !translation.downvoted">
																			{{translation.downvotes}} people downvoted
																		</small>
																	</div>
																	<div class="col-md-12 options">
																		<a href ng-click="translation.showEdit = false" ng-show="translation.showEdit">Cancel Edit</a>
																		<a href ng-click="handleEdit(translation.result_id)" ng-show="!translation.showEdit && userID == translation.author.user_id">Edit
																		</a>
																		<a href ng-click="deleteTranslation(translation.result_id); translation.upvoted = !translation.upvoted; translation.downvoted = (translation.upvoted) ? translated.downvoted : !translation.downvoted;" ng-show="translation.author.user_id == userID">Delete</a>
																		<a href ng-click="upvote(translation.result_id)" ng-style="translation.upvoteStyle">
																			<span class="glyphicon glyphicon-thumbs-up" style="font-size:1em;"></span>
																			Upvote
																		</a>
																		<a href ng-click="downvote(translation.result_id)" ng-style="translation.downvoteStyle">
																			<span class="glyphicon glyphicon-thumbs-down" style="font-size:1em;"></span>
																			Downvote
																		</a>
																		<a href ng-click="showCommentBox = true" ng-show="!showCommentBox">Comment</a>
																	</div>
																</div>
															</div>
															<div class="col-md-4">
																<div class="authorBox">
																	Answered on {{translation.date_posted}}
																	<br/>
																	<img ng-src="{{translation.author.avatar}}" style="width:auto;height:50px;">
																	<a ng-href="profile.php?id={{translation.author.user_id}}">
																		{{translation.author.username}}
																		<br/>
																		{{translation.author.points}} reputation
																		<br/>
																	</a>
																</div>
															</div>
														</div>
													</div>
	<!-- 												<div class="col-md-12">
														<hr>
													</div> -->
													<div class="col-md-12">
														<div ng-show="showCommentBox">
															<textarea ng-model="data.commentBox" class="form-control"></textarea>
															<button ng-click="postTranslationComment(translation.result_id)" class="btn btn-primary btn-xs">Submit</button>
															<a href ng-click="showCommentBox = false" ng-show="showCommentBox">Cancel</a>
														</div>
													</div>
												</div>
											</p>
											<div class="translationComment" ng-repeat="comment in translation.comments | limitTo:data.x_top_comments">
												<div class="row">
													<div class="col-md-12">
														<a ng-href="profile.php?id={{comment.author.user_id}}" style="float:left;">{{comment.author.username}}</a>
														<span style="float:right;"><span ng-show="comment.author.user_id == userID"><a href ng-click="deleteComment(comment.comment_id)">Delete</a> | </span><small>Posted {{comment.date_posted}}</small></span>
													</div>
													<div class="col-md-12">
														<hr>
													</div>
													<div class="col-md-12">
														<p ng-bind-html="comment.comment_text">
														</p>
													</div>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<p class="text-center" ng-show="data.x_top_comments < translations[0].comments.length">
														<a href ng-click="incrementTopComments()">Load More Comments</a>
													</p>
												</div>
											</div>
										</div>
										<div ng-show="!translation.note && translation.default_show">
											<form ng-show="translation.showEdit == 2">
												<p class="help-block">
													An existing function in the desintation language sufficient as an equivalent to <?php echo $from_function->function_name;?>
												</p>
												<angucomplete-alt	
													id="members"
									              placeholder="Search functions/methods in <?php echo $to_language->language_name;?>"
									              pause="400"
									              selected-object="translation.edittedLinkedFunction"
									              remote-url="http://paraphrase.io/postRequests/info/searchFunctionInLanguage.php?lan=<?php echo $to_language->language_id;?>&function="
									              title-field="title"
									              description-field="description"
									              input-class="form-control form-control-small"/>
									             </angucomplete-alt>
									            <input type="submit" ng-click="editTranslation(translation.result_id)" value="Submit Changes" class="btn btn-primary btn-sm">
											</form>
											<form ng-show="translation.showEdit == 3">
												<div class="form-group">
													<label for="translation"><strong>Translation</strong></label>
													<p class="help-block">
														Your primary "translation"/answer to be attached to your post
													</p>
													<textarea ng-model="translation.edittedTranslation" class="form-control" ng-show="translation.showEdit" rows="5"></textarea>
												</div>
												<div class="form-group">
													<label for="comments" ng-show="data.postType == 1"><strong>Summary</strong></label>
													<p class="help-block">Any additional comments/explanations/notes/details you want to add, possibly elaborating the answer or alternative answers.</p>
													<textarea rows="10" class="form-control" ng-model="translation.edittedSummary" name="comment"></textarea>
												</div>
												<div class="form-group" ng-show="translation.edittedSummary">
													<p class="help-block">
														Preview
													</p>
													<div marked="translation.edittedSummary">
													</div>
												</div>
												<input type="submit" ng-click="editTranslation(translation.result_id)" class="btn btn-primary" value="Submit Changes">
											</form>											
											<a ng-href="function.php?id={{translation.suggested_function.function_id}}" ng-if="translation.single">
												<div hljs source="translation.suggested_function.function_name" language="<?php echo strtolower($language2->language_name);?>" ng-show="!translation.showEdit">
												</div>
											</a>
											<div hljs source="translation.suggested_function.function_name" language="<?php echo strtolower($language2->language_name);?>" ng-if="!translation.single" ng-show="!translation.showEdit">
											</div>
											<strong ng-show="!translation.showEdit">Summary</strong>
											<p ng-show="translation.single && !translation.showEdit" ng-bind-html="translation.suggested_function.description">
												<a ng-href="function.php?id={{translation.suggested_function.function_id}}">
													<?php echo $to_language->language_name . "'s built in {{translation.suggested_function.function_name}} <br/>";
													?>
												</a>
										<!-- 		{{translation.suggested_function.description}} -->
											</p>
											<p ng-show="!translation.single && !translation.showEdit">
												<div marked="translation.comment" ng-show="!translation.showEdit">
												</div>
										<!-- 		{{translation.comment}} -->
											</p>
											<span ng-show="translation.single && !translation.showEdit"><strong>Syntax</strong></span>
											<blockquote ng-show="translation.single && !translation.showEdit">
												<div hljs source="translation.suggested_function.syntax" language="<?php echo strtolower($language2->language_name);?>" id="no">
												</div>
											</blockquote>
											<div class="row" style="margin-bottom:10px;">
												<div class="col-md-12">
													<hr style="padding:left:3px;padding-right:3px;margin-bottom:3px;padding-bottom:0px;padding-top:0px;margin-top:3px;opacity:0.5">
												</div>
												<div class="col-md-12">
													<div class="row" style="margin-bottom:5px;">
														<div class="col-md-8">
															<div class="row">
																<div class="col-md-12">
																	<small ng-show="translation.upvoted">
																		You <span ng-show="translations[0].upvotes > 1"> and {{translation.upvotes - 1}} other(s) upvoted </span><span ng-show="translations[0].upvotes == 1"> upvoted this
																	</small>
																	<small ng-show="(translation.upvotes > 0) && !translation.upvoted">
																		{{translation.upvotes}} people upvoted
																	</small>
																	<span ng-show="(translation.upvotes > 0) && (translation.downvotes > 0)" class="glyphicon glyphicon-asterisk" aria-hidden="true" style="font-size:0.5em;top:50%;"></span>
																	<small ng-show="translation.downvoted">
																		You <span ng-show="translation.downvotes > 1"> and {{translation.downvotes - 1}} other(s) downvoted </span><span ng-show="translation.downvotes == 1"> downvoted this</span>
																	</small>
																	<small ng-show="(translation.downvotes > 0) && !translation.downvoted">
																		{{translation.downvotes}} people downvoted
																	</small>
																</div>
																<div class="col-md-12 options">
																	<a href ng-click="adminDelete(translation.result_id)" ng-show="data.admin">Admin_Delete</a>
																	<a href ng-click="handleEdit(translation.result_id)" ng-show="!translation.showEdit && userID == translation.author.user_id">Edit</a>
																	<a href ng-click="translation.showEdit = 0" ng-show="translation.showEdit">Cancel Edit</a>
																	<a href ng-click="deleteTranslation(translation.result_id); translation.upvoted = !translation.upvoted; translation.downvoted = (translation.upvoted) ? translated.downvoted : !translation.downvoted;" ng-show="translation.author.user_id == userID">Delete</a>
																	<a href ng-click="upvote(translation.result_id)" ng-style="translation.upvoteStyle">
																		<span class="glyphicon glyphicon-thumbs-up" style="font-size:1em;"></span>
																		Upvote
																	</a>
																	<a href ng-click="downvote(translation.result_id)" ng-style="translation.downvoteStyle">
																		<span class="glyphicon glyphicon-thumbs-down" style="font-size:1em;"></span>
																		Downvote
																	</a>
																	<a href ng-click="showCommentBox = true" ng-show="!showCommentBox">Comment</a>
																</div>
															</div>
														</div>
														<div class="col-md-4">
															<div class="authorBox">
																Answered on {{translation.date_posted}}
																<br/>
																<img ng-src="{{translation.author.avatar}}" style="width:auto;height:50px;">
																<a ng-href="profile.php?id={{translation.author.user_id}}">
																	{{translation.author.username}}
																	<br/>
																	{{translation.author.points}} reputation
																	<br/>
																</a>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="comments" style="margin-top:20px;">
												<div class="translationComment" ng-repeat="comment in translation.comments | limitTo:data.x_top_comments">
													<div class="row">
														<div class="col-md-12">
															<a ng-href="profile.php?id={{comment.author.user_id}}" style="float:left;">{{comment.author.username}}</a>
															<span style="float:right;"><span ng-show="comment.author.user_id == userID"><a href ng-click="deleteComment(comment.comment_id)">Delete</a> | </span><small>Posted {{comment.date_posted}}</small></span>
														</div>
														<div class="col-md-12">
															<hr style="opacity: 0.8">
														</div>
														<div class="col-md-12">
															<p ng-bind-html="comment.comment_text">
															</p>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-12">
														<p class="text-center" ng-show="data.x_top_comments < translations[0].comments.length">
															<a href ng-click="incrementTopComments()">Load More Comments</a>
														</p>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div ng-show="showCommentBox">
													<textarea ng-model="data.commentBox" class="form-control"></textarea>
													<button ng-click="postTranslationComment(translation.result_id)" class="btn btn-primary btn-xs">Submit</button>
													<a href ng-click="showCommentBox = false" ng-show="showCommentBox">Cancel</a>
												</div>
											</div>
										</div>
										<div ng-show="!translation.default_show" style="margin-bottom:10px;">
											<div class="row">
												<div class="col-md-8">
													<a href ng-click="showHiddenAnswer(translation.result_id)">
														<i>
															Answer hidden due to high downvotes, click to show.
														</i>
													</a>
												</div>
												<div class="col-md-4">
													<div class="authorBox">
														Answered on {{translation.date_posted}}
														<br/>
														<img ng-src="{{translation.author.avatar}}" style="width:auto;height:50px;">
														<a ng-href="profile.php?id={{translation.author.user_id}}">
															{{translation.author.username}}
															<br/>
															{{translation.author.points}} reputation
															<br/>
														</a>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<hr ng-show="!$last" style="opacity:1;margin-bottom:30px;">
											</div>
										</div>
									</div>
								</div>
								<p class="text-center" ng-show="translations.length == 0 && !submitted">
									<strong>
										No answers or notes found. Be the first to submit one below!
									</strong>
								</p>
<!-- 								<div ng-show="translations.length == 0">
									<form ng-show="!submitted">
										<p class="text-center">
											<strong>
												No answers or notes found. Be the first to submit one below!
											</strong>
										</p>
										<div class="form-group">
											<label class="col-md-2 control-label" style="padding-left:0px;"><strong>Post Type</strong></label>
											<div class="col-md-10">
												<div style="float:right;">
													<label class="radio-inline">
														<input type="radio" ng-model="data.postType" value="1">Translation
													</label>				
													<label class="radio-inline">
														<input type="radio" ng-model="data.postType" value="2">Note
													</label>
												</div>
											</div>
											<br/>
											<p class="help-block" ng-show="data.postType == 1">
												An additional (or revised) translation along with a recommended but optional explanation/summary
											</p>
											<p class="help-block" ng-show="data.postType == 2">
												A standalone note/annotation that includes relevant and helpful information to the current translation or other related material. 
											</p>
										</div>
										<div class="form-group" ng-show="data.postType == 1">
											<label for="translation"><strong>Translation</strong></label>
											<p class="help-block">The primary translation to be attached to your post and will be shown first
												<label class="checkbox-inline" style="float:right;">
													<input type="checkbox" ng-model="data.single" ng-true-value="true" ng-false-value="false">Single, existing function <a href ng-click="singleExpand()">[?]</a>
												</label>
											</p>
											<angucomplete-alt	
												id="members"
								              placeholder="Search functions/methods in <?php //echo $to_language->language_name;?>"
								              pause="400"
								              selected-object="data.linked_function"
								              remote-url="http://paraphrase.io/postRequests/info/searchFunctionInLanguage.php?lan=<?php //echo $to_language->language_id;?>&function="
								              title-field="title"
								              description-field="description"
								              input-class="form-control form-control-small"
								              ng-show="data.single"/>
								             </angucomplete-alt>
											<textarea rows="5" class="form-control" ng-model="data.translation" ng-show="!data.single"></textarea>
										</div>
										<div class="form-group">
											<label for="comments" ng-show="data.postType == 1"><strong>Summary</strong></label>
											<label for="comment" ng-show="data.postType == 2"><strong>Note</strong></label>
											<p class="help-block">Any additional comments/explanations/</p>
											<textarea rows="10" class="form-control" ng-model="data.comment" name="comment"></textarea>
										</div>
										<div class="form-group" ng-show="data.postType == 1 && data.comment">
											<p class="help-text">
												<strong>
													Preview
												</strong>
											</p>
											<div marked="data.comment">
											</div>
										</div>
										<input type="submit" ng-click="submitNoteTranslation()" class="btn btn-default">
									</form>
								</div>		 -->			
							</tab>
						</tabset>
<!-- 				<div class="row">
					<div class="col-md-12">
						<div ng-controller="comments" ng-show="translations.length > 1"> -->
<!-- 							<h3>Additional Translations and Notes</h3>
							<hr>
							<div ng-repeat="translation in translations.slice(1)" class="translationAnswer">
								<div class="row" ng-if="!translation.type">
									<div class="col-md-12">
										<p>
											<div hljs source="translation.suggested_function.function_name" language="<?php //echo strtolower($language2->language_name);?>">	
											</div>
										</p>
									</div>
									<div class="col-md-12">
										<p>
											{{translation.comment}}
										</p>
									</div>
									<div class="col-md-12">
										<small ng-show="translation.upvoted">
											You <span ng-show="translation.upvotes > 1"> and {{translation.upvotes - 1}} other(s) upvoted </span><span ng-show="translation.upvotes == 1"> upvoted this
										</small>
										<small ng-show="(translation.upvotes > 0) && !translation.upvoted">
											{{translation.upvotes}} people upvoted
										</small>
										<span ng-show="(translation.upvotes > 0) && (translation.downvotes > 0)" class="glyphicon glyphicon-asterisk" aria-hidden="true" style="font-size:0.5em;top:50%;"></span>
										<small ng-show="translation.downvoted">
											You <span ng-show="translation.upvotes > 1"> and {{translation.downvotes - 1}} other(s) downvoted </span><span ng-show="translation.downvotes == 1"> downvoted this</span>
										</small>
										<small ng-show="(translation.downvotes > 0) && !translation.downvoted">
											{{translation.downvotes}} people downvoted
										</small>
									</div>
									<div class="col-md-8 options">
										<a href ng-click="deleteTranslation(translation.result_id); translation.upvoted = !translation.upvoted; translation.downvoted = (translation.upvoted) ? translated.downvoted : !translation.downvoted;" ng-show="translation.author.user_id == userID">Delete</a>
										<a href ng-click="upvote(translation.result_id)">Upvote</a>
										<a href ng-click="downvote(translation.result_id)">Downvote</a>
										<a href ng-click="deleteTranslation(translation.result_id)" ng-if="profileID == translation.author.user_id">Delete</a>
									</div>
									<div class="col-md-4">
										<div class="userAvatar">
											<a ng-href="profile.php?id={{translation.author.user_id}}">{{translation.author.username}}</a>
										</div>
									</div>
								</div>
								<div class="row" ng-if="translation.type == 'note'">
									<div class="col-md-12">
										<p>
											{{translation.comment}}
										</p>
									</div>
									<div class="col-md-12">
										<small ng-show="translation.upvoted">
											You <span ng-show="translation.upvotes > 1"> and {{translation.upvotes - 1}} other(s) upvoted </span><span ng-show="translation.upvotes == 1"> upvoted this
										</small>
										<small ng-show="(translation.upvotes > 0) && !translation.upvoted">
											{{translation.upvotes}} people upvoted
										</small>
										<span ng-show="(translation.upvotes > 0) && (translation.downvotes > 0)" class="glyphicon glyphicon-asterisk" aria-hidden="true" style="font-size:0.5em;top:50%;"></span>
										<small ng-show="translation.downvoted">
											You <span ng-show="translation.upvotes > 1"> and {{translation.downvotes - 1}} other(s) downvoted </span><span ng-show="translation.downvotes == 1"> downvoted this
										</small>
										<small ng-show="(translation.downvotes > 0) && !translation.downvoted">
											{{translation.downvotes}} people downvoted
										</small>
									</div>
									<div class="col-md-8">
										<a href ng-click="deleteTranslation(translation.result_id); translation.upvoted = !translation.upvoted; translation.downvoted = (translation.upvoted) ? translated.downvoted : !translation.downvoted;" ng-show="translation.author.user_id == userID">Delete</a>
										<a href ng-click="upvote(translation.result_id)">Upvote</a>
										<a href ng-click="downvote(translation.result_id)">Downvote</a>
									</div>
									<div class="col-md-4">
										<div class="userAvatar">
											<a ng-href="profile.php?id={{translation.author.user_id}}">{{translation.author.username}}</a>
										</div>
									</div>
								</div>
								<hr>
							</div>
						</div>
						<div ng-show="translations.length == 1">
							<p class="text-center">
								No additional translations or notes found. You can submit one below
							</p>
						</div> -->
							<div ng-show="!submitted && translations.length != 0">
								<h3>Your Translation/Note</h3>
								<hr>
							</div>
							<form ng-show="!submitted">
								<div class="form-group">
									<label class="col-md-2 control-label" style="padding-left:0px;"><strong>Post Type</strong></label>
									<div class="col-md-10">
										<div style="float:right;">
											<label class="radio-inline">
												<input type="radio" ng-model="data.postType" value="1">Answer
											</label>				
											<label class="radio-inline">
												<input type="radio" ng-model="data.postType" value="2">Note
											</label>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12" ng-show="data.postType == 1">
											<p class="help-block">
												<small>
												A main translation along with a recommended but optional explanation/summary
												</small>
											</p>
										</div>
										<div class="col-md-12" ng-show="data.postType == 2">
											<p class="help-block">
												<small>
												A standalone note/annotation that includes relevant and helpful information to the current translation or other related material. 
												</small>
											</p>
										</div>
									</div>
								</div>
								<div class="form-group" ng-show="data.postType == 1">
									<label for="translation"><strong>Translation</strong></label>
									<div class="row">
										<div class="col-md-12">
											<p class="help-block">
												<small>
													The primary translation to be attached to your post. Pre-set styles applied
												</small>
												<label class="checkbox-inline" style="float:right;">
													<input type="checkbox" ng-model="data.single" ng-true-value="true" ng-false-value="false">Single function <a href ng-click="singleExpand()">[?]</a>
												</label>
											</p>
											<angucomplete-alt	
												id="members"
								              placeholder="Search functions/methods in <?php echo $to_language->language_name;?>"
								              pause="400"
								              selected-object="data.linked_function"
								              remote-url="http://paraphrase.io/postRequests/info/searchFunctionInLanguage.php?lan=<?php echo $to_language->language_id;?>&function="
								              title-field="title"
								              description-field="description"
								              input-class="form-control form-control-small"
								              ng-show="data.single"/>
								            </angucomplete-alt>
								          	<p ng-show="data.single" class="help-text">
								          		<small>
								          			Don't see your function here? It likely hasn't been indexed. You can add it <a href="add.php">here</a>
								          		</small>
								          	</p>
											<textarea rows="5" class="form-control" ng-model="data.translation" ng-show="!data.single"></textarea>
										</div>
									</div>
								</div>
								<div class="form-group" ng-show="data.translation && data.postType == 1">
									<p class="help-text">
										<strong>Translation Preview</strong>
									</p>
									<div hljs source="data.translation" language="<?php echo strtolower($to_language->language_name);?>">
									</div>
								</div>
								<hr style="opacity:0.5" ng-show="data.postType == 1">
								<div class="form-group">
										<label for="comment" ng-show="data.postType == 1"><strong>Summary</strong></label>
										<label for="comment" ng-show="data.postType == 2"><strong>Note</strong></label>
										<p class="help-block" ng-show="data.postType == 1">
											<small>
												Any additional comments/notes/alternative answers or details you wish to elaborate or add to make your answer more complete. Markdown is supported, you can view its syntax <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet">here</a>
											</small>
										</p>
										<p class="help-block" ng-show="data.postType == 2">
											<small>
												Markdown supported, you can view its syntax <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet">here</a>
											</small>
										</p>
									<textarea rows="10" class="form-control" ng-model="data.comment" name="comment"></textarea>
								</div>
								<div class"form-group" ng-show="data.postType == 1 && data.comment">
									<p class="help-text">
										<strong>
											Preview
										</strong>
									</p>
									<div marked="data.comment">
									</div>
								</div>
								<div class"form-group" ng-show="data.postType == 2 && data.comment">
									<p class="help-text">
										<strong>
											Preview
										</strong>
									</p>
									<div marked="data.comment">
									</div>
								</div>
								<input type="submit" ng-click="submitNoteTranslation()" class="btn btn-default">
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/highlightjs/highlight.pack.js"></script>
<script src="dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="dependencies/angular-highlight/angular-highlightjs.min.js"></script>
<script src="bower_components/angular-sanitize/angular-sanitize.min.js"></script>
<script src="bower_components/showdown/src/showdown.js"></script>
<script src="/bower_components/angucomplete-alt/dist/angucomplete-alt.min.js"></script>
<script src="dependencies/chartjs/Chart.min.js"></script>
<script src="bower_components\angular-chart.js\dist\angular-chart.js"></script>
<script src="bower_components/marked/lib/marked.js"></script>
<script src="bower_components/angular-marked/angular-marked.js"></script>
<script src="angular/translate/translate.js"></script>