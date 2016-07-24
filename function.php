<?php
error_reporting(-1);
try {	
	//Autoloading script
	function __autoload($class_name) {
		/*
		Last chance for PHP script to call a class name
		 */
		$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}
	$user_id = User::get_current_user_id();
/*	require_once 'includes/LanguageFunction.php';
	require_once 'includes/Language.php';
	require_once 'includes/FunctionParameter.php';*/
	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
		//Attempt to create the function
		$function_id = $_GET['id'];
		$mysqli = Database::connection();

		$test_function = new LanguageFunction(array(
			'function_id' => $function_id)
		);

		if (LanguageFunction::function_exists($test_function)) {

			//First try to see if the user has voted for the catgorization already
			if (is_numeric($user_id)) {
				$sql = "SELECT category_id FROM `categorization_project` WHERE user_id = '$user_id' AND function_id = '$function_id'";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				if ($result->num_rows == 1) {
					$vote = mysqli_fetch_row($result)[0];
				}
				else {
					$vote = null;
				}
			}

			require_once 'navbar.php';

			///Get function specs
			$sql = "SELECT t1.function_id, t1.function_name, t1.language as `language_id`, t1.category_id, t1.link, t1.description as `description`, t1.syntax, t1.user_id, t1.type, t1.date, t2.language_name, t4.super_id, t4.description as `super_description`, t2.icon, t1.big_o_average, t1.big_o_average_summary, t1.big_o_worst_case, t1.big_o_worst_case_notes, t1.big_o_best_case, t1.big_o_best_case_notes
			FROM functions as t1 
			LEFT JOIN languages as t2
			ON t2.language_id = t1.language
			LEFT JOIN category as t3
			ON t3.category_id = t1.category_id
			LEFT JOIN super_category as t4 
			ON t4.super_id = t3.type
			WHERE t1.function_id = '$function_id'";
			//echo $sql;
			$result = $mysqli->query($sql)
			or die($mysqli->error);
			if ($result->num_rows == 1) {
				$row = mysqli_fetch_array($result);
				$row['function_language'] = new Language(array(
					'language_id' => $row['language_id'],
					'language_name' => $row['language_name'],
					'icon' => $row['icon'])
				);
				$super_description = $row['super_description'];
				$function = new LanguageFunction($row);

	/*			//Get the input and outputs
				$sql = "SELECT parameter_id, parameter_description, parameter_name, type
				FROM function_parameters
				WHERE function_id = '$function->function_id'
				";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				$function->parameters = array(); //Holding array for the parameters of the function
				while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
					$parameter = new FunctionParameter($row);
					array_push($function->parameters, $parameter);
				}*/
				$category_id = $row['category_id'];
				$language_id = $function->function_language->language_id;
				$super_id = $row['super_id'];
				//Now get the related functions to this function
				$sql = "SELECT t1.function_id, t1.function_name, t1.language as `language_id`, t1.category_id, t1.link, t1.description as `description`, t4.description as `super_description`
				FROM functions as t1
				LEFT JOIN super_category as t4
				ON t4.super_id = t1.category_id
				WHERE t1.language = '$language_id'
				AND t1.function_id != '$function->function_id'
				ORDER BY t1.category_id = '$category_id' DESC, t4.super_id = '$super_id' DESC
				LIMIT 20
				";
				$result = $mysqli->query($sql)
				or die ($mysqli->error);
				$related_functions = array(); //Array to hold the related functions to the current function
				while ($row_x = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
					$row_x['function_language'] = $function->function_language;
					$temp_function = new LanguageFunction($row_x);
					$temp_function->super_description = $row_x['super_description'];
					array_push($related_functions, $temp_function);
				}
			}
			else {
				throw new UnexpectedValueException;
			}
		}
		else {
			throw new UnexpectedValueException;
		}
	}
	else {
		throw new UnexpectedValueException;	
	}
}
catch (UnexpectedValueException $e) {
	header('Location: http://paraphrase.io/404.php');
}
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete-alt.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/function.css">
		<link rel="stylesheet" href="css/highlightjs/tomorrow.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">
		<link rel="stylesheet" type="text/css" href="dependencies/angular-charts/angular-chart.css">
		<title><?php echo $function->function_language->function_name . "'s " . $function->function_name;?></title>
	</head>
	<body ng-controller="main" class="container-fluid" ng-init="init('<?php echo $function->function_id;?>', '<?php echo $user_id;?>', '<?php echo $vote;?>')">
		<div class="col-md-10 col-md-offset-1">
<!-- 			<ul class="breadcrumb" style="padding-left:0px">
				<li><a href="#">Polyphrase</a></li>
				<li class="active"><a href="<?php echo 'language.php?id=' . $function->function_language->language_id;?>"><?php echo $function->function_language->language_name?></a>
				</li>
				<li>Indexed Functions</li>
				<li>
					<?php echo $super_description;?>
				</li>
				<li>
					<?php echo $function->function_name;?>
				</li>
			</ul> -->
				<div class="row">
					<div class="col-md-9">
						<input type="text" ng-model="alternate" class="form-control" placeholder="Search a function/method/concept to be paraphrased in another language" ng-show="inputType == 'multi'">
						<angucomplete-alt
							id="members"
			              placeholder="Search functions/descriptions"
			              pause="400"
			              selected-object="searchPhrase"
			              remote-url="http://paraphrase.io/postRequests/addFunction/searchFunction.php?function="
			              title-field="title"
			              description-field="description"
			              input-class="form-control form-control-small"
			              input-changed="updateScope"
			              ng-show="inputType == 'function'">
					</div>
			        <div class="col-md-2">
						<select ng-model="selectedLanguageID" class="form-control" ng-options="language.language_id as language.language_name for language in languages">
						</select>
			        </div>
			        <div class="col-md-1">
			        	<button class="btn btn-primary" ng-click="attemptTranslation()" style="float:right;display:block;">Search</button>
			        </div>
			        <div class="col-md-7">
						<small>Try searching a function like: "strtoupper()" or a description "Creating an array from an array-like object"</small>
			        </div>
			        <div class="col-md-5">
			        	<p class="text-right">
			        		<small style="text-align:right;display:block;">Supported languages: PHP, Javascript, C, Python, Ruby, Swift, Java, C++, C#</small>
			        	</p>
			        </div>
			    </div>
			<h2 style="display:inline;margin-bottom:0px;"><?php echo $function->function_language->language_name . "'s " . $function->function_name;?></h2>
			<hr style="margin-top:0px;margin-bottom:0px;">
			<div class="row">
				<div class="col-md-2" style="margin-top:15px;">
					<tabset vertical="true" type="pills">
						<tab heading="Documentation" select="switchTab('Documentation')"></tab>
						<tab heading="Translations" select="switchTab('Translations')"></tab>
						<tab heading="Function Notes" select="switchTab('Notes')"></tab>
						<tab heading="Categorization" select="switchTab('Categorization')"></tab>
					</tabset>
				</div>
				<div class="col-md-10" ng-show="data.tab == 'Notes'">
					<div class="row">
						<div class="col-md-8">
							<h3>Function Notes</h3>
						</div>
						<div ng-show="data.functionNotes.length < 1">
							<div class="col-md-8">
								<h6>No function notes to show, be the first to post one below!</h6>
							</div>
						</div>
						<div ng-repeat="functionNote in data.functionNotes" class="functionNote">
							<div class="col-md-8">
								<div class="row">
									<div class="col-md-12">
										<div marked="functionNote.note_text">
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12 feedbackBar">
										<small ng-show="functionNote.upvotes > 0">
											<span ng-show="functionNote.upvoted">
												You 
												<span ng-show="functionNote.upvotes > 1">
													and {{functionNote.upvotes - 1}} others upvoted
												</span>
												<span ng-show="functionNote.upvotes == 1">
													have upvoted this
												</span>
											</span>
											<span ng-show="!functionNote.upvoted">
												{{functionNote.upvotes}} people upvoted
											</span>
										</small>
										<span ng-show="(functionNote.upvotes > 0) && (functionNote.downvotes > 0)" class="glyphicon glyphicon-asterisk" aria-hidden="true" style="font-size:0.5em;top:50%;"></span>
										<small>
											<span ng-show="functionNote.downvotes > 0">
												You 
												<span ng-show="functionNote.downvotes > 1">
													and {{functionNote.downvotes - 1}} others downvoted
												</span>
												<span ng-show="functionNote.downvotes == 1">
													have downvoted this
												</span>
											</span>
										</small>
									</div>
									<div class="col-md-8 optionBar">
										<a href ng-click="deleteNote(functionNote.note_id)" ng-show="userID == functionNote.author.user_id">Delete</a>
										<a href ng-click="upvoteFunctionNote(functionNote.note_id)" ng-style="functionNote.upvoteStyle">
										<span class="glyphicon glyphicon-thumbs-up" style="font-size:1em;"></span>
										Upvote<span ng-show="functionNote.upvoted">d</span></a>
										<a href ng-click="downvoteFunctionNote(functionNote.note_id)" ng-style="functionNote.downvoteStyle">
										<span class="glyphicon glyphicon-thumbs-down" style="font-size:1em;"></span>Downvote<span ng-show="functionNote.downvoted">d</span></a>
									</div>
									<div class="col-md-4">
										<div class="authorBox">
											<small>Posted {{functionNote.date_posted}}</small>
											<br/>
											<img ng-src="{{functionNote.author.avatar}}">
											<a ng-href="profile.php?id={{functionNote.author.user_id}}">
												{{functionNote.author.username}}
												<br/>
												{{functionNote.author.points}} reputation
											</a>
										</div>
									</div>
									<!--
									<div class="col-md-4 authorBox">
										<small>Posted {{functionNote.date_posted}}</small>
										<img ng-src="{{functionNote.author.avatar}}" width="50">
										<h5><strong>{{functionNote.author.username}}</strong></h5>
									</div>
									-->
									<div class="row" ng-show="!$last">
										<div class="col-md-12">
											<hr>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-8" style="margin-top:20px;">
							<h3>Post your function note</h3>
							<form>
								<textarea ng-model="data.functionNoteBox" class="form-control" rows="8">
								</textarea>
								<p class="help-text">
									<small>
									Markdown (and HTML) supported editor. You can view markdown syntax <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet">here</a>
									</small>
								</p>
								<div class="form-group" ng-show="data.functionNoteBox">
									<p class="help-text">
										<strong>
											Preview
										</strong>
									</p>
									<div marked="data.functionNoteBox">
									</div>
								</div>
								<input type="submit" ng-click="postNote()" class="btn btn-primary" value="Submit">
							</form>
						</div>
					</div>
				</div>

				<div class="col-md-10" ng-show="data.tab == 'Categorization'">
					<div class="row">
						<div class="col-md-8">
							<h3>Categorization Project</h3>
							<small>
								We're trying to get relevant data on functions and syntax of languages to improve the site and map out functions across languages! You can help us by voting for what you think most well describes this syntax/function
							</small>
							<div class="row">
								<div class="col-md-8" ng-show="data.showSelect">
									<select ng-model="data.votedSuperID" ng-options="super.super_id as super.description for super in data.superCategories" class="form-control">
									</select>
								</div>
								<div class="col-md-4" ng-show="data.showSelect">
									<a href ng-click="categorizeFunction()">
										<button class="btn btn-info">Vote</button>
									</a>
								</div>
								<div class="col-md-12" ng-show="!data.showSelect">
									<p class="text-center">
										<strong>
											You have voted for `{{data.selectedSuperDescription}}`.
										</strong> 
											<a href ng-click="data.showSelect = true">Undo</a>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-10" ng-show="data.tab == 'Translations'">
					<div class="row" ng-repeat="relatedTranslation in data.relatedTranslations">
						<div class="col-md-12">
							<div class="row">
								<div class="col-md-1">
									<h3 style="text-align:center;">
										{{relatedTranslation.upvotes}}
									</h3>
									<p class="text-center" style="text-align:center;">
										upvotes
									</p>
								</div>
								<div class="col-md-8">
									<h3>
										<a ng-href="translate.php?ffi={{functionID}}&lan={{relatedTranslation.language_id}}">
										<?php
										echo $function->function_language->language_name . "'s " . $function->function_name . ' equivalent in {{relatedTranslation.language_name}}';
										?>
										</a>
									</h3>
									<p>
									<?php
										echo $function->description;
									?>
									</p>
								</div>
								<div class="col-md-1">
									<h3 style="text-align:center;">
										{{relatedTranslation.answers}}
									</h3>
									<p style="text-align:center;">
										answers
									</p>
								</div>
								<div class="col-md-1">
									<h3 style="text-align:center;">
										{{relatedTranslation.views}}
									</h3>
									<p style="text-align:center;">
										views
									</p>
								</div>
								<div class="col-md-12">
									<small>
										<a href="#">
											Last updated: {{relatedTranslation.last_updated}} ago
										</a>
									</small>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
						</div>
					</div>
				</div>
				<div class="col-md-10" ng-show="data.tab == 'Documentation'">
					<div class="row">
						<div class="col-md-4 col-md-push-8">
							<h3>Related Functions</h3>
							<?php 
							//Loop through the related functions
								foreach ($related_functions as $key=>$related_function) {
									echo '<div class="relatedFunction">';
									echo '<a href="function.php?id=' . $related_function->function_id . '">' . $related_function->function_name . '</a>';
									echo '<small>' . $related_function->super_description . '</small>';
									echo '</div>';
								}
							?>
						</div>
						<div class="col-md-8 col-md-pull-4">
							<p>
								<h3>Overview</h3>
								<?php echo $function->description;?>
								<h3>Syntax</h3>
								<div hljs source="'<?php echo $function->syntax;?>'">
								</div>
								<div class="row" style="margin-bottom:15px;">
									<div class="col-md-12">
										<h3 style="display:inline;margin-bottom:5px;">Parameters</h3>
										<a href ng-click="data.newParameters.pop()">
											<span class="glyphicon glyphicon-minus" style="float:right;margin-left:10px;" ng-if="data.newParameters.length > 0">
										</a>
										<a href ng-click="incrementNewParameters()">
											<span class="glyphicon glyphicon-plus" style="float:right;" aria-hidden="true"></span>
										</a>
									</div>
									<div class="col-md-12" ng-show="functionParameters.length == 0">
										<h6 style="text-align:center;">No parameters to show</h6>
									</div>
									<div class="col-md-12 parameter" ng-repeat="parameter in functionParameters">
										<h5 style="display:inline;padding-top:" ng-show="!parameter.showEdit"><strong>{{parameter.parameter_name}}</strong></h5>
										<a href ng-click="deleteParameter(parameter.parameter_id)" ng-if="parameter.showEdit">
											<span class="glyphicon glyphicon-remove" style="float:right;" aria-hidden="true"></span>
										</a>
										<a href ng-click="parameter.showEdit = true; parameter.editName = parameter.parameter_name; parameter.editDescription = parameter.parameter_description">
											<span class="glyphicon glyphicon-pencil" style="float:right;" aria-hidden="true"></span>
										</a>
										<form ng-show="parameter.showEdit">
											<input type="text" ng-model="parameter.editName" class="form-control">
											<br/>
											<textarea ng-model="parameter.editDescription" class="form-control" rows="5"></textarea>
											<input type="submit" value="Submit" class="btn btn-info" ng-click="editParameter(parameter.parameter_id, parameter.editName, parameter.editDescription, 7); parameter.showEdit = false">
											<a href ng-click="parameter.showEdit = false">Cancel</a>
										</form>
										<p ng-show="!parameter.showEdit" ng-bind-html="parameter.parameter_description">
										</p>
									</div>
									<div class="col-md-12" ng-show="data.newParameters.length > 0" ng-repeat="newParam in data.newParameters">
										<form>
											<input type="text" ng-model="newParam.parameter_name" class="form-control" placeholder="Name or representation of the parameter, preferrably in the syntax">
											<br/>
											<textarea ng-model="newParam.parameter_description" class="form-control" rows="5" placeholder="A descrpition of the parameter"></textarea>
											<hr>
										</form>
									</div>
									<div class="col-md-1" ng-show="data.newParameters.length > 0">
										<button class="btn btn-info" ng-click="submitParameters()">Submit</button>
									</div>
									<?php
		/*							foreach ($function->parameters as $key=>$parameter) {
										if ($paramter->type != 'Output') {
											?>
											<p>
												<h5 style="display:inline;"><strong><?php echo $parameter->parameter_name?></strong></h5>
												<a href ng-click="editParameter('<?php echo $parameter->paramter_id;?>')">
													<span class="glyphicon glyphicon-pencil" style="float:right;" aria-hidden="true"></span>
												</a>
												<h6><?php echo $paramter->parameter_description;?></h6>
											</p>
											<?php
										}
									}*/
									?>
								</div>
								<div class="row">
									<div class="col-md-12">
										<h3 style="display:inline;margin-bottom:5px;">Returns</h3>
										<a href ng-click="data.newReturns.pop()">
											<span class="glyphicon glyphicon-minus" style="float:right;margin-left:10px;" ng-if="data.newReturns.length > 0">
										</a>
										<a href ng-click="incrementNewReturns()">
											<span class="glyphicon glyphicon-plus" style="float:right;" aria-hidden="true"></span>
										</a>
									</div>
									<div class="col-md-12" ng-show="functionReturns.length == 0">
										<h6 style="text-align:center;">No returns to show</h6>
									</div>
									<div class="col-md-12 parameter" ng-repeat="return in functionReturns">
										<h5 style="display:inline;" ng-show="!return.showEdit"><strong>{{return.parameter_name}}</strong></h5>
										<a href ng-click="deleteReturn(return.parameter_id)" ng-if="return.showEdit">
											<span class="glyphicon glyphicon-remove" style="float:right;" aria-hidden="true"></span>
										</a>
										<a href ng-click="return.showEdit = true; return.editName = return.parameter_name; return.editDescription = return.parameter_description">
											<span class="glyphicon glyphicon-pencil" style="float:right;" aria-hidden="true"></span>
										</a>
										<form ng-show="return.showEdit">
											<input type="text" ng-model="return.editName" class="form-control">
											<br/>
											<textarea ng-model="return.editDescription" class="form-control" rows="5"></textarea>
											<input type="submit" value="Submit" class="btn btn-info" ng-click="editParameter(return.parameter_id, return.editName, return.editDescription, 8); return.showEdit = false">
											<a href ng-click="return.showEdit = false">Cancel</a>
										</form>
										<p ng-show="!return.showEdit" ng-bind-html="return.parameter_description">
										</p>
									</div>
									<div class="col-md-12" ng-show="data.newReturns.length > 0" ng-repeat="newReturn in data.newReturns">
										<form>
											<input type="text" ng-model="newReturn.parameter_name" class="form-control" placeholder="Name or representation of the parameter, preferrably in the syntax">
											<br/>
											<textarea ng-model="newReturn.parameter_description" class="form-control" rows="5" placeholder="A descrpition of the parameter"></textarea>
											<hr>
										</form>
									</div>
									<div class="col-md-1" ng-show="data.newReturns.length > 0">
										<button class="btn btn-info" ng-click="submitReturns()">Submit</button>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<h3 style="display:inline;margin-bottom:5px;">Time Complexity</h3>
										<?php
										if (!($function->big_o_average_case && $function->big_o_worst_case && $function->big_o_best_case)) {
											?>
											<a href ng-click="data.showAddBigO = true">
												<span class="glyphicon glyphicon-plus" style="float:right;" aria-hidden="true"></span>
											</a>
											<?php
										}
									?>
									</div>
									<?php
									if (!($function->big_o_average_case || $function->big_o_best_case || $function->big_o_worst_case)) {
										?>
										<div class="col-md-12">
											<p class="text-center">
												<h6 style="text-align:center;">No time complexity performance to show</h6>
											</p>
										</div>
										<?php
									}
									if ($function->big_o_average_case) {
										?>
										<div class="col-md-12 bigO">
											<h5 style="display:inline;"><strong>Average (Expected): </h5>
											<?php
												$big_o = $function->big_o_average_case;
												if (strpos($function->big_o_average_case, '^')) {
													$big_o = explode('^', $function->big_o_average_case);
													$big_o = $big_o[0] . '<sup>' . $big_o[1] . '</sup>';
												}
												echo $big_o . '</strong>';
											?>
											<p>
												<a href ng-click="data.editAverageCase = true; data.editAverageCaseValue = '<?php echo $function->big_o_average_case;?>'; data.editAverageCaseNotes = <?php echo htmlentities(json_encode($function->big_o_average_summary));?>;">
													<span class="glyphicon glyphicon-pencil" style="float:right;" aria-hidden="true"></span>
												</a>
												<?php
												if ($function->big_o_average_summary) {
													echo nl2br($function->big_o_average_summary);
												}
												else {
													echo 'No notes found';
												}
												?>
											</p>
										</div>							
										<div class="col-md-12" ng-show="data.editAverageCase">
											<form>
												<input type="text" ng-model="data.editAverageCaseValue" class="form-control">
												<br/>
												<textarea ng-model="data.editAverageCaseNotes" rows="5" class="form-control">
												</textarea>
												<input type="submit" ng-click="editBigO(2, data.editAverageCaseValue, 3, data.editAverageCaseNotes)" value="Submit" class="btn btn-primary btn-sm">
											</form>
										</div>
										<?php
									}
									if ($function->big_o_best_case) {
										?>
										<div class="col-md-12 bigO">
											<h5 style="display:inline;"><strong>Best Case: </h5>
											<?php
												$big_o = $function->big_o_best_case;
												if (strpos($function->big_o_best_case, '^')) {
													$big_o = explode('^', $function->big_o_best_case);
													$big_o = $big_o[0] . '<sup>' . $big_o[1] . '</sup>';
												}
												echo $big_o . '</strong>';
											?>
											<p>
												<a href ng-click="data.editBestCase = true; data.editBestCaseValue = '<?php echo $function->big_o_best_case;?>'; data.editBestCaseNotes = '<?php echo $function->big_o_best_case_notes;?>';">
													<span class="glyphicon glyphicon-pencil" style="float:right;" aria-hidden="true"></span>
												</a>
												<?php
												if ($function->big_o_best_case_notes) {
													echo nl2br($function->big_o_best_csae_notes);
												}
												else {
													echo 'No notes found';
												}
												?>
											</p>
										</div>
										<div class="col-md-12" ng-show="data.editBestCase">
											<form>
												<input type="text" ng-model="data.editBestCaseValue" class="form-control">
												<br/>
												<textarea ng-model="data.editBestCaseNotes" rows="5" class="form-control" placeholder="Explanation">
												</textarea>
												<input type="submit" ng-click="editBigO(6, data.editBestCaseValue, 7, data.editBestCaseNotes)" value="Submit" class="btn btn-primary btn-sm">
											</form>
										</div>
										<?php
									}
									if ($function->big_o_worst_case) {
										?>
										<div class="col-md-12 bigO">
											<h5 style="display:inline;"><strong>Worst Case: </h5>
											<?php
												$big_o = $function->big_o_worst_case;
												if (strpos($function->big_o_worst_case, '^')) {
													$big_o = explode('^', $function->big_o_worst_case);
													$big_o = $big_o[0] . '<sup>' . $big_o[1] . '</sup>';
												}
												echo $big_o . '</strong>';
											?>
											<p>
												<a href ng-click="data.editWorstCase = true; data.editWorstCaseValue = '<?php echo $function->big_o_worst_case;?>'; data.editWorstCaseNotes = '<?php echo $function->big_o_worst_case_notes;?>';">
													<span class="glyphicon glyphicon-pencil" style="float:right;" aria-hidden="true"></span>
												</a>
												<?php
												if ($function->big_o_worst_case_notes) {
													echo nl2br($function->big_o_worst_case_notes);
												}
												else {
													echo 'No notes found';
												}
												?>
											</p>
										</div>
										<div class="col-md-12" ng-show="data.editWorstCase">
											<form>
												<input type="text" ng-model="data.editWorstCaseValue" class="form-control">
												<br/>
												<textarea ng-model="data.editWorstCaseNotes" rows="5" class="form-control">
												</textarea>
												<input type="submit" ng-click="editBigO(4, data.editWorstCaseValue, 5, data.editWorstCaseNotes)" value="Submit" class="btn btn-primary btn-sm">
											</form>
										</div>
										<?php
									}
									?>
									<div class="col-md-12" ng-show="data.showAddBigO">
										<form>
											<div class="row">
												<div class="col-md-4">
													<select ng-options="bigO.column as bigO.label for bigO in data.selectableBigO" ng-model="data.addedBigO" class="form-control">
													</select>
												</div>
												<div class="col-md-8">
													<input type="text" class="form-control" placeholder="Big O Notation for case" ng-model="data.addBigOCase" required>
												</div>
											</div>
											<div class="row">
												<div class="col-md-12">
													<textarea ng-model="data.addBigOEdit" class="form-control" rows="5" placeholder="Explanation">
													</textarea>
												</div>
											</div>
											<div class="row">
												<div class="col-md-2">
													<input type="submit" ng-click="addBigO()" value="Submit" class="btn btn-primary">
												</div>
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/highlightjs/highlight.pack.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="bower_components/angular-sanitize/angular-sanitize.min.js"></script>
<script src="/dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="dependencies/angular-highlight/angular-highlightjs.min.js"></script>
<script src="/bower_components/angucomplete-alt/dist/angucomplete-alt.min.js"></script>
<script src="bower_components/marked/lib/marked.js"></script>
<script src="bower_components/angular-marked/angular-marked.js"></script>
<script src="angular/function/function.js"></script>