<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
if (isset($_GET['lan'])) {
	$lan = $_GET['lan'];
	$languages = array(
		'all' => 'All',
		'php' => 'PHP',
		'javascript' => 'Javascript',
		'python' => 'Python',
		'ruby' => 'Ruby'
	);
	if (!array_key_exists($lan, $languages)) {
		$lan = 'all';
	}
	$language_name = $languages[$lan];
}
else {
	$mysqli = Database::connection();
	$sql = "SELECT COUNT(function_id)
	FROM `categorization_project`
	WHERE date_voted = CURDATE()";
	$result = $mysqli->query($sql)
	or die ($mysqli->error);
	$votes = mysqli_fetch_row($result)[0];
}
require_once __DIR__ . '/includes/User.php';
$user_id = User::get_current_user_id();
spl_autoload_register('__autoload');
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="/css/angucomplete-alt.css">
		<link rel="stylesheet" type="text/css" href="/css/basic.css">
		<link rel="stylesheet" type="text/css" href="/css/index.css">
		<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="/css/ngDialog/ngDialog-theme-default.css">
		<link rel="icon" href="http://paraphrase.io/ui/orange.ico">
		<style type="text/css">
			.nav-tabs>li {
  				float: right;
			}
		</style>
		<title>
			Paraphrase
		</title>
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="init('<?php echo $user_id;?>', '<?php echo $lan;?>')">
		<?php require_once __DIR__ . '/navbar.php';?>
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
<!-- 				<div class="row">
					<div class="col-md-12">
						<h2 style="display:inline;padding-bottom:0px;">
							<img src="ui/coding.png" height="42" style="margin:right:5px;display:inline;"> Paraphrase Translations
						</h2>
					</div>
					<div class="col-md-12">
						<hr style="margin-bottom:2px;margin-top:0px;">
						{{stats.requests}} Translation Requests, {{stats.answers}} Answers
					</div>
				</div> -->

<!-- 				<div class="row">
					<div class="col-md-12">
						<button class="btn btn-info btn-sm" style="float:right;margin-left:5px;" ng-click="askQuestion()">Ask Question</button>
						<button class="btn btn-primary btn-sm" style="float:right;" ng-click="askQuestion()">Ask Paraphrase</button>
					</div>
				</div> -->
				<div class="row">
					<div class="col-md-12">
						<p class="text-center">
							Paraphrase is an attempt at a community solution to make grasping new programming languages easier and faster by abridging the tedious syntax translation for common concepts and providing relevant details to elaborate on languages functions. 
							<p class="text-center">
								<strong>Next Update:</strong> September 10, 2015, <strong>Reputation Priveleges and User Roles systems falls in place: </strong> September 15, 2015

							</p>
						</p>
					</div>
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
						<small>
							<marked style="float:left;padding-left:0px;padding-right:0px;">Try translating a function like: `Array.pop()` or a looking up a function like `array_push`
							</marked>
						</small>
				<!-- 		<small>Ignore selection of the typehead to do a general search on the entire site, i.e. for a function description</small>	  -->       
			        </div>
			        <div class="col-md-5">
			        	<p class="text-right">
			        		<small style="text-align:right;display:block;">Supported languages: PHP, Javascript, Python, Ruby. Pending Languages: Swift, C++ (and variants), Java</small>
			        	</p>
			        </div>
			    </div>
				<div class="row">
					<div class="col-md-9" style="margin-top:10px;">
<!-- 						<h4 style="display:inline;">Questions</h4>
						<ul class="list-inline" style="display:inline;float:right;">
							<li>
								<strong>Currently Viewing:</strong> {{viewMessage}}
							</li>
							<li>
								|
							</li>
							<li>
								<a href ng-click="to = false">From</a>
							</li>
							<li>
								<a href ng-click="to = true">To
								</a>
							</li>
						</ul>
						<span class="label label-info" style="display:inline-block;">All</span>
						<div class="jumbotron" style="padding:20px;margin-bottom:5px">
							{{curLanguage.summary | limitTo:1000}}.....
						</div> -->
						<tabset>
							<form class="navbar-form navbar-left" style="margin-left:0px;padding-left:0px;">
								<h4 style="margin-left:0px;padding-left:0px;" ng-show="data.language.language_id != 11">Tagged Questions {{data.view.name}} <?php echo $language_name;?></h4>
								<h4 style="margin-left:0px;padding-left:0px;" ng-show="data.language.language_id == 11">Questions</h4>
							</form>
							<li ng-style="data.lastTabStyle" class="ng-isolate-scope">
								<span dropdown>
									<a href dropdown-toggle>
										View: {{data.view.name}} <span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href ng-click="changeView()">Switch view to: {{data.view.falseName}}
										</a>
									</ul>
								</span>
							</li>
							<tab heading="This Month" deselect="styleTab()" select="unstyleTab()">
								<div class="jumbotron" style="padding:20px;margin-bottom:5px">
									{{data.language.summary | limitTo:1000}}
									<a ng-href="language.php?id={{data.language.language_id}}" style="float:right;" ng-show="data.language.language_id != 11">View Language Page</a>
								</div>
								<div class="row">
									<div ng-repeat="translation in translationsMonthFiltered">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-9" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?ffi={{translation.from_function.function_id}}&lan={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
												{{translation.from_function.description}}
											</p>
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.answers}}
											</h4>
											Answers
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.views}}
											</h4>
											<p style="text-align:center">
												Views
											</p>
										</div>
										<div class="col-md-12" style="margin-top:0px;">
											<small><a href"#">{{translation.last_activity_message}}</a></small>		
											<hr style="margin-top:5px;margin-bottom:5px;">
										</div>
									</div>
								</div>
								<div ng-show="translationsMonthFiltered.length == 0">
									<h5 style="text-align:center;">No translations to show</h5>
								</div>
							</tab>
							<tab heading="This Week">
								<div class="jumbotron" style="padding:20px;margin-bottom:5px">
									{{data.language.summary | limitTo:1000}}
									<a ng-href="language.php?id={{data.language.language_id}}" style="float:right;" ng-show="data.language.language_id != 11">View Language Page</a>
								</div>
								<div class="row">
									<div ng-repeat="translation in translationsWeekFiltered">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-9" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?ffi={{translation.from_function.function_id}}&lan={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
												{{translation.from_function.description}}
											</p>
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.answers}}
											</h4>
											Answers
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.views}}
											</h4>
											<p style="text-align:center">
												Views
											</p>
										</div>
										<div class="col-md-12" style="margin-top:0px;">
											<small><a href"#">{{translation.last_activity_message}}</a></small>		
											<hr style="margin-top:5px;margin-bottom:5px;">
										</div>
									</div>
									<div ng-show="translationsWeekFiltered.length == 0" class="col-md-12">
										<h5 style="text-align:center;">No translations to show</h5>
									</div>
								</div>
							</tab>
							<tab heading="Fewest Answers">
								<div class="jumbotron" style="padding:20px;margin-bottom:5px">
									{{data.language.summary | limitTo:1000}}
									<a ng-href="language.php?id={{data.language.language_id}}" style="float:right;" ng-show="data.language.language_id != 11">View Language Page</a>
								</div>
								<div class="row">
									<div ng-repeat="translation in fewestAnswersFiltered" ng-cloak>
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-9" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?ffi={{translation.from_function.function_id}}&lan={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
												{{translation.from_function.description}}
											</p>
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.answers}}
											</h4>
											Answers
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.views}}
											</h4>
											<p style="text-align:center">
												Views
											</p>
										</div>
										<div class="col-md-12" style="margin-top:0px;">
											<small><a href"#">{{translation.last_activity_message}}</a></small>		
											<hr style="margin-top:5px;margin-bottom:5px;">
										</div>
									</div>
								</div>
								<div ng-show="fewestAnswersFiltered.length == 0">
									<h5 style="text-align:center;">No translations to show</h5>
								</div>
							</tab>
				    		<tab heading="New">
								<div class="jumbotron" style="padding:20px;margin-bottom:5px">
									{{data.language.summary | limitTo:1000}}
									<a ng-href="language.php?id={{data.language.language_id}}" style="float:right;" ng-show="data.language.language_id != 11">View Language Page</a>
								</div>
				    			<div class="row" style="margin-bottom:0px;">
									<div ng-repeat="translation in translationsFiltered">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-9" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?ffi={{translation.from_function.function_id}}&lan={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
												{{translation.from_function.description}}
											</p>
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.answers}}
											</h4>
											Answers
										</div>
										<div class="col-md-1" style="margin-bottom:0px;">
											<h4 style="text-align:center;">
												{{translation.views}}
											</h4>
											<p style="text-align:center">
												Views
											</p>
										</div>
										<div class="col-md-12" style="margin-top:0px;">
											<small><a href"#">{{translation.last_activity_message}}</a></small>		
											<hr style="margin-top:5px;margin-bottom:5px;">
										</div>
									</div>
								</div>
								<div ng-show="translationsFiltered.length == 0">
									<h5 style="text-align:center;">No translations to show</h5>
								</div>
							</tab>
							<tab heading="Hot" active="true">
								<div class="jumbotron" style="padding:20px;margin-bottom:5px">
									{{data.language.summary | limitTo:1000}}
									<a ng-href="language.php?id={{data.language.language_id}}" style="float:right;" ng-show="data.language.language_id != 11">View Language Page</a>
								</div>
				    			<div class="row" style="margin-bottom:0px;">
				    				<div infinite-scroll="scrollingFunction()" infinite-scroll-distance="3">
										<div ng-repeat="translation in featuredTranslationsFiltered" ng-cloak>
											<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
													<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
													margin:0 auto;"></span></a>
													<h4 style="text-align:center;">{{translation.upvotes}}</h4>
											</div>
											<div class="col-md-9" style="margin-bottom:0px;">
												<p>
													<h5><a ng-href="translate.php?ffi={{translation.from_function.function_id}}&lan={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
													{{translation.from_function.description}}
												</p>
											</div>
											<div class="col-md-1" style="margin-bottom:0px;">
												<h4 style="text-align:center;">
													{{translation.answers}}
												</h4>
												Answers
											</div>
											<div class="col-md-1" style="margin-bottom:0px;">
												<h4 style="text-align:center;">
													{{translation.views}}
												</h4>
												<p style="text-align:center">
													Views
												</p>
											</div>
											<div class="col-md-12" style="margin-top:0px;">
												<small><a href"#">{{translation.last_activity_message}}</a></small>		
												<hr style="margin-top:5px;margin-bottom:5px;">
											</div>
										</div>
									</div>
									<div ng-show="featuredTranslationsFiltered.length == 0">
										<h5 style="text-align:center;">No translations to show</h5>
									</div>
								</div>
							</tab>
<!-- 							<form class="navbar-form navbar-right">
								<div class="form-group">
									<input type="text" class="form-control" placeholder="Search functions/methods" ng-model="data.searchPhrase" style="width:250px">
								</div>
								<a href="#" class="btn btn-info">Search</a>
							</form> -->
						</tabset>

					</div>
					<div class="col-md-3" style="min-height:100vh;margin-top:10px;">
						<div class="sideBar">
							<div class="languageStats" ng-show="data.language.language_id != 11">
								<div>
									<h4 style="padding-bottom:0px;margin-bottom:0px;display:inline-block;">
										<?php echo $language_name;?>
									</h4>
									<small style="display:inline-block;float:right;padding-top:10px;">
										<a href="stats.php?tab=stats">View Stats
											<span class="glyphicon glyphicon-chevron-right">
											</span>
										</a>
									</small>
								</div>
								<small style="margin-bottom:0px;padding-bottom:0px;">
									{{data.language.rankingMessage}}
								</small>
							</div>
							<hr ng-show="data.language.language_id != 11">
							<div style="margin-bottom:10px;" ng-show="data.language.language_id != 11">
								<h4 style="padding-bottom:0px;margin-bottom:0px;">{{data.to}}</h4>
								<small>questions have been asked towards this language</small>
		<!-- 						<h4 style="display:inline;">Questions To:</h4>
								<span style="float:right;">
									{{data.to}}
								</span> -->
							</div>
							<div style="margin-bottom:10px;" ng-show="data.language.language_id != 11">
								<h4 style="padding-bottom:0px;margin-bottom:0px;">{{data.from}}</h4>
								<small>
									questions have been asked from this language
								</small>
		<!-- 						<h4 style="display:inline;">Questions From:</h4>
								<span style="float:right;">
									{{data.from}}
								</span> -->
							</div>
							<div style="margin-bottom:10px;" ng-show="data.language.language_id == 11">
								<div>
									<h4 style="padding-bottom:0px;margin-bottom:0px;display:inline-block;">{{data.to}}</h4>
									<small style="display:inline-block;float:right;padding-top:10px;">
										<a href="stats.php?tab=stats">View Stats
											<span class="glyphicon glyphicon-chevron-right">
											</span>
										</a>
									</small>
								</div>
								<small>questions have been asked in total</small>
							</div>
<!-- 							<a href ng-repeat="language in languages" ng-class="language.classStyle" ng-click="newSelect(language.language_id)" ng-cloak>{{language.language_name}}<span style="float:right;">
								<span class="badge" ng-show="selected != language.language_id &&  to">{{infoTo[language.language_id] || 0}}</span>
								<span class="badge" ng-show="selected != language.language_id && !to">{{infoFrom[language.language_id] || 0}}</span>
								<span class="badge" ng-show="selected == language.language_id && to" style="color:#474949;background-color:#ffffff">{{infoTo[language.language_id] || 0}}</span>
								<span class="badge" ng-show="selected == language.language_id && !to" style="color:#474949;background-color:#ffffff">{{infoFrom[language.language_id] || 0}}</span>
								<small>requests</small>
							</span></a> -->
							<hr style="margin-bottom:10px;margin-top:0px;padding-top:0px;">
							<div ng-show="data.language.language_id == 11" style="margin-bottom:10px;">
								<div>
									<h4 style="padding-bottom:0px;margin-bottom:0px;display:inline-block;"><?php echo $votes;?></h4>
									<small style="display:inline-block;float:right;padding-top:10px;">
										<a href="updatelog.php">View About
											<span class="glyphicon glyphicon-info-sign">
											</span>
										</a>
									</small>
								</div>
								<small>votes today towards the categorization project</small>
							</div>
							<hr style="margin-bottom:10px;margin-top:0px;padding-top:0px;" ng-show="data.language.language_id == 11">
							<div>
								<div>
									<h4 style="display:inline-block;">Top Users</h4>
									<small style="display:inline-block;float:right;padding-top:10px;">
										<a href="stats.php?tab=users">Show More
											<span class="glyphicon glyphicon-stats"></span>
										</a>
									</small>
								</div>
								<div ng-repeat="user in topUsers" style="margin-bottom:5px;height:30px;" ng-cloak>
									<a ng-href="profile.php?id={{user.user_id}}"><img ng-src="{{user.avatar}}" width="30px" height="30px" class="img-circle"></a>
									<a ng-href="profile.php?id={{user.user_id}}" style="line-height: 30px; vertical-align: middle;">
									{{user.username}}<span style="vertical-align: middle;float:right;">{{user.points}} rep.</span>
									</span></a>
								</div>
								<hr>
								<div>
									<h4 style="display:inline-block;">Active Functions</h4>
									<small style="display:inline-block;float:right;padding-top:10px;">
										<a href="add.php">Contribute
											<span class="glyphicon glyphicon-plus"></span>
										</a>
									</small>
								</div>
								<div>
									<small style="display:inline-block;">
										Function Name
									</small>
									<small style="float:right;display:inline;">Notes</small>
								</div>
								<div style="margin-top:5px;">
									<div ng-repeat="function in data.recentAnnotatedFunctions" style="margin-bottom:5px;" ng-cloak>
										<a ng-href="language.php?id={{function.function.function_language.language_id}}">{{function.function_language.language_name}}'s</a>
										<a ng-href="function.php?id={{function.function_id}}">{{function.function_name}}</a>
										<span class="badge" style="float:right;">{{function.notes}}</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<div>
		</div>
	</body>
</html>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-66949201-1', 'auto');
  ga('send', 'pageview');

</script>
<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script src="/dependencies/angularJS/angular.min.js"></script>
<script src="/dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="/dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="/bower_components/angucomplete-alt/dist/angucomplete-alt.min.js"></script>
<script src="/bower_components/ngInfiniteScroll/build/ng-infinite-scroll.min.js"></script>
<script src="bower_components/marked/lib/marked.js"></script>
<script src="bower_components/angular-marked/angular-marked.js"></script>
<script src="/angular/translations/translations_new.js"></script>