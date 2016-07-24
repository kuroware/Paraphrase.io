<?php
require_once 'navbar.php';
require_once 'includes/user.php';
$user_id = User::get_current_user_id();
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/angucomplete/angucomplete.css">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/index.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog-theme-default.css">
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="init('<?php echo $user_id;?>')">
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
				<div class="row">
					<form>
						<div class="col-md-9">
							<angucomplete id="members"
					              placeholder="Search functions/methods"
					              pause="400"
					              selectedobject="searchCode"
					              url="http://localhost/codetranslator/postrequests/addFunction/searchFunction.php?function="
					              titlefield="title"
					              descriptionfield = "summary"
					              inputclass="form-control form-control-small" required>
					        </angucomplete>
				        </div>
				        <div class="col-md-2">
							<select ng-model="selectedLanguageID" class="form-control" required>
								<option ng-repeat="language in selectableLanguages" ng-selected="language.language_id == selectedLanguageID" ng-value="language.language_id" ng-show="language.language_id != 11">{{language.language_name}}</option>
							</select>
				        </div>
				        <div class="col-md-1">
				        	<input type="submit" class="btn btn-primary" ng-click="attemptTranslation()" value="Translate">
				       	</div>
				    </form>
				</div>
				<div class="row">
					<div class="col-md-3" style="min-height:100vh;margin-top:10px;">
						<div class="list-group">
							<div style="margin-bottom:10px;">
								<h4 style="display:inline;">Languages</h4>
							</div>
							<a href ng-repeat="language in languages" ng-class="language.classStyle" ng-click="newSelect(language.language_id)">{{language.language_name}}<span style="float:right;">
								<span class="badge" ng-show="selected != language.language_id &&  to">{{infoTo[language.language_id] || 0}}</span>
								<span class="badge" ng-show="selected != language.language_id && !to">{{infoFrom[language.language_id] || 0}}</span>
								<span class="badge" ng-show="selected == language.language_id && to" style="color:#474949;background-color:#ffffff">{{infoTo[language.language_id] || 0}}</span>
								<span class="badge" ng-show="selected == language.language_id && !to" style="color:#474949;background-color:#ffffff">{{infoFrom[language.language_id] || 0}}</span>
								<small>requests</small>
							</span></a>
							<hr>
							<div>
								<h4>Top Users</h4>
								<div ng-repeat="user in topUsers" style="margin-bottom:5px;height:30px;">
									<a ng-href="profile.php?id={{user.user_id}}"><img ng-src="{{user.avatar}}" width="30px" height="30px" class="img-circle"></a>
									<a ng-href="profile.php?id={{user.user_id}}" style="line-height: 30px; vertical-align: middle;">
									{{user.username}}<span style="vertical-align: middle;float:right;">{{user.points}} rep.</span>
									</span></a>
								</div>
								<hr>
								<h4 style="display:inline">New Functions</h4>
								<span style="float:right;display:inline;">Translations</span>
								<div style="margin-top:5px;">
									<div ng-repeat="function in newFunctions" style="margin-bottom:5px;">
										<a ng-href="language.php?id={{function.function_language.language_id}}">{{function.function_language.language_name}}'s</a>
										<a ng-href="function.php?id={{function.function_id}}">{{function.function_name}}</a>
										<span class="badge" style="float:right;">{{function.counter}}</span>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-9" style="margin-top:10px;">
						<h4 style="display:inline;">Equivalences</h4>
						    <div class="btn-group" dropdown dropdown-append-to-body style="display:inline;float:right;">
						      <button type="button" class="btn btn-primary btn-xs dropdown-toggle" dropdown-toggle ng-disabled="selected == 11">
						        Currently Viewing: {{viewMessage}} <span class="caret"></span>
						      </button>
						      <ul class="dropdown-menu" role="menu">
						        <li><a href ng-click="to = false" ng-show="to">Change View: From</a></li>
						        <li><a href ng-click="to = true" ng-show="!to">Change View: To</a></li>
						      </ul>
						    </div>
<!-- 							<span class="label label-default" style="float:right;display:inline;padding-top:5px;" ng-show="!to">Showing: From</span>
							<span class="label label-default" style="float:right;display:inline;padding-top:5px;" ng-show="to">Showing: To</span>
							<input type="checkbox" ng-model="to" style="float:right;display:inline;padding-top:5px;" ng-true-value="true" ng-false-value="false"> -->
						<span class="label label-info" style="display:inline-block;">All</span>
						<div class="jumbotron" style="padding:20px;margin-bottom:5px">
							{{curLanguage.summary | limitTo:1000}}.....
						</div>
<!-- 						<h4>Filter</h4>
						<div class="row">
							<form>
								<div class="col-md-6 col-md-offset-2">
									<angucomplete id="members"
							              placeholder="Search functions/methods"
							              pause="400"
							              selectedobject="code1"
							              url="http://localhost/codetranslator/postrequests/addFunction/searchFunction.php?function="
							              titlefield="title"
							              descriptionfield = "summary"
							              inputclass="form-control form-control-small">
							        </angucomplete>
								</div>
								<div class="col-md-1">
								</div>
								<div class="col-md-2">
									<select ng-model="lan2" class="form-control">
										<option ng-repeat="language in languages" ng-selected="language.language_id == default" ng-value="language.language_id">{{language.language_name}}</option>
									</select>
								</div>
								<div class="col-md-1">
									<input type="submit" ng-click="translate()" class="btn btn-default" value="Search">
								</div>
							</form>
						</div> -->
						<tabset>
							<tab heading="Featured">
				    			<div class="row" style="margin-bottom:0px;">
									<div ng-repeat="translation in featuredTranslationsFiltered" ng-controller="translationRequest">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-8" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?src={{translation.from_function.function_name}}&lan1={{translation.from_function.function_language.language_id}}&lan2={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
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
							</tab>
				    		<tab heading="New">
				    			<div class="row" style="margin-bottom:0px;">
									<div ng-repeat="translation in translationsFiltered" ng-controller="translationRequest">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-8" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?src={{translation.from_function.function_name}}&lan1={{translation.from_function.function_language.language_id}}&lan2={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
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
							<tab heading="Fewest Answers">
								<div class="row">
									<div ng-repeat="translation in fewestAnswersFiltered" ng-controller="translationRequest">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-8" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?src={{translation.from_function.function_name}}&lan1={{translation.from_function.function_language.language_id}}&lan2={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
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
							<tab heading="This Week">
								<div class="row">
									<div ng-repeat="translation in translationsWeekFiltered" ng-controller="translationRequest">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-8" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?src={{translation.from_function.function_name}}&lan1={{translation.from_function.function_language.language_id}}&lan2={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
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
							<tab heading="This Month">
								<div class="row">
									<div ng-repeat="translation in translationsMonthFiltered" ng-controller="translationRequest">
										<div class="col-md-1" style="padding-top:0.5%;margin-bottom:0px;">
												<a href ng-click="upvoteRequest(translation.translation_id);increment();"><span class="glyphicon glyphicon-chevron-up" aria-hidden="true" style="display:table;
												margin:0 auto;"></span></a>
												<h4 style="text-align:center;">{{translation.upvotes}}</h4>
										</div>
										<div class="col-md-8" style="margin-bottom:0px;">
											<p>
												<h5><a ng-href="translate.php?src={{translation.from_function.function_name}}&lan1={{translation.from_function.function_language.language_id}}&lan2={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
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
							<form class="navbar-form navbar-right" role="search">
								<div class="form-group">
									<input type="text" class="form-control" placeholder="Search functions/methods" ng-model="data.searchPhrase">
								</div>
								<a href="#" class="btn btn-info">Search</a>
							</form>
						</tabset>

					</div>
				</div>.
			<div>
		</div>
	</body>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="dependencies/angucomplete-master/angucomplete.js"></script>
<script src="angular/translations/translations.js"></script>