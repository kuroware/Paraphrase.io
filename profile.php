<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if (($class_name == 'OutgoingTranslation') || ($class_name == 'IncomingTranslation')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	elseif (strpos($class_name, 'Comment')) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Comment.php';
	}
	else {
		require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
	}
}
require_once 'navbar.php';
/*require_once 'includes/user.php';*/
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$profile_id = $_GET['id'];
	$profile = new User(array('user_id' => $profile_id)
	);
	$profile->get_fields();
}
else {
	die('Unable to find user');
}
if (isset($_GET['tab'])) {
	$tab = $_GET['tab'];
}
else {
	$tab = 'sum';
}
$user_id = User::get_current_user_id();
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/profile.css">
		<title><?php echo $profile->username;?></title>
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="init('<?php echo $profile_id;?>', '<?php echo $user_id;?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="row">
					<div class="col-md-11">
						<h2 style="display:inline;margin-left:0px;"><?php
						if ($tab != 'sum') {
							$mysqli = Database::connection();
							$sql = "SELECT username FROM users WHERE user_id = '$profile_id'";
							$result = $mysqli->query($sql)
							or die ($mysqli->error);
							$username = mysqli_fetch_row($result)[0];
							echo $username;
						}
						?>{{profile.username}}</h2>
					</div>
					<div class="col-md-1">
						<a ng-href="editprofile.php" ng-show="userID == profileID" class="btn btn-info" style="float:right;" aria-label="Left Align">Edit Profile<span class="glyphicon glyphicon-pencil" aria-hidden="true" style="margin-left:5px;"></span></a>
					</div>
					<div class="col-md-12">
						<ul class="list-inline">
							<li>
								<a ng-href="profile.php?id={{profileID}}&tab=sum">Summary</a>
							</li>
							<li>
								<a ng-href="profile.php?id={{profileID}}&tab=qs">Questions
								</a>
							</li>
							<li>
								<a ng-href="profile.php?id={{profileID}}&tab=a">Answers</a>
							</li>
							<li>
								<a ng-href="profile.php?id={{profileID}}&tab=rep">Reputation</a>
							</li> 		
						</ul>
					</div>
				</div>
				<div ng-if="'<?php echo $tab;?>' == 'rep'" ng-init="getGraph()">
					<div ng-if="data && labels">
						<canvas id="line" class="chart chart-line" data="data"
						  labels="labels" click="onClick" height="95%">
						</canvas> 
					</div>
					<!-- Load the reputation changes into a table for now-->
					<table class="table table-striped table-hover ">
						<thead>
							<tr>
								<th>Date</th>
								<th>Change</th>
								<th>Reason</th>
							</tr>
						</thead>
						<tbody>
						<tr ng-repeat="change in data.filteredUserRepChanges">
							<td>{{change.date}}</td>
							<td style="color:green;" ng-if="change.change >= 0">+{{change.change}}</td>
							<td style="color:red;" ng-if="change.change < 0">{{change.change}}</td>
							<td>
								<a ng-href="function.php?id={{repChange.linked_id}}" ng-show="change.type == 5">
									Added {{change.message}}
								</a>
								<a ng-href="translate.php?ffi={{change.result_spec[0]}}&lan={{change.result_spec[1]}}" ng-if="(change.type >= 1) && (change.type <= 4)">
									{{change.message}} answer/note 
									<span ng-show="change.type == 1">
									upvoted
									</span>
									<span ng-show="change.type == 2">
									downvoted
									</span>
									<span ng-show="change.type == 3">
									un-upvoted
									</span>
									<span ng-show="change.type == 4">
									un-downvoted
									</span>
								</a>
								<a ng-href="translate.php?ffi={{change.result_spec[0]}}&lan={{change.result_spec[1]}}" ng-if="(change.type == 15) || (change.type == 16)">
									{{change.message}} question upvoted
								</a>
							</td>
						</tr>
					</table>
				    <pagination 
				      ng-model="data.currentPage"
				      total-items="userRepChanges.length"
				      max-size="data.maxSize"
				      boundary-links="true">
				    </pagination>
				</div>
				<div ng-if="'<?php echo $tab;?>' == 'a'" ng-init="getAnswers()">
					<div class="row" ng-repeat="translation in submittedTranslations">
						<div class="col-md-1">
							<h4 style="text-align:center;">
								{{translation.upvotes}}
							</h4>
							<p style="text-align:center;">
								Upvotes
							</p>
						</div>
						<div class="col-md-9">
							<p>
								<h5><a ng-href="translate.php?ffi={{translation.from_function.function_id}}&lan={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
								{{translation.comment}}
							</p>
						</div>
						<div class="col-md-2">
							<h4 style="text-align:center;">
								{{translation.date_posted_ago}}
							</h4>
							<p style="text-align:center;">
								posted ago
							</p>
						</div>
					</div>
					<div class="row" ng-if="submittedTranslations.length == 0">
						<p class="text-center">
							<h5 style="text-align:center;"><strong>No answers or notes yet</strong></h5>
						</p>
					</div>
				</div>
				<div ng-if="'<?php echo $tab;?>' == 'qs'" ng-init="getQuestions()">
					<div class="row" ng-repeat="request in requests">
						<div class="col-md-1">
							<h4 style="text-align:center;">
								{{request.upvotes}}
							</h4>
							<p style="text-align:center;">
								Upvotes
							</p>
						</div>
						<div class="col-md-7">
							<h5><a ng-href="translate.php?src={{request.from_function.function_name}}&lan1={{request.from_function.function_language.language_id}}&lan2={{request.to_language.language_id}}">{{request.from_function.function_language.language_name}}'s {{request.from_function.function_name}} equivalent in {{request.to_language.language_name}}</h5></a>
							{{request.from_function.description | limitTo:100}}....
						</div>
						<div class="col-md-1">
							<h4 style="text-align:center;">
								{{request.answers}}
							</h4>
							<p style="text-align:center;">
								Answers
							</p>
						</div>
						<div class="col-md-1" style="margin-bottom:0px;">
							<h4 style="text-align:center;">
								{{request.views}}
							</h4>
							<p style="text-align:center">
								Views
							</p>
						</div>
						<div class="col-md-2">
							<h4 style="text-align:center;">
								{{request.date_requested}}
							</h4>
							<p style="text-align:center;">
								requested ago
							</p>
						</div>
					</div>
					<div class="row" ng-if="requests.length == 0">
						<p class="text-center">
							<h5 style="text-align:center;"><strong>No questions asked yet</strong></h5>
						</p>
					</div>
				</div>
				<div ng-if="'<?php echo $tab;?>' == 'sum'" ng-init="getSummary()">
					<div class="row">
						<div class="col-md-12">
							<div class="row">
								<div class="col-md-2">
									<img ng-src="{{profile.avatar}}" width="150">
									<h4>About</h4>
									<p style="float:left;line-height:5px;">
										<h5 class="leftbar" style="margin-top:0px;line-height:140%">
										Title
										<br/>
										Profile Views
										<br/>
										Date Joined
										</h5>
									</p>
									<p style="text-align:right;line-height:140%">
										AI
										<br/>
										{{ ::profile.views}}
										<br/>
										{{ ::profile.date_joined}}
									</p>
									<div style="clear:both;"></div>
									<h4>Stats</h4>
									<p style="float:left;line-height:5px;">
										<h5 class="leftbar" style="margin-top:0px;line-height:140%">
										Upvotes
										<br/>
										Downvotes
										<br/>
										U/D Ratio
										<br/>
										Answers
										<br/>
										Questions
										</h5>
									</p>
									<p style="text-align:right;line-height:140%">
										{{ ::profile.upvotes}}
										<br/>
										{{ ::profile.downvotes}}
										<br/>
										{{ ::profile.ud_ratio | limitTo:4}}
										<br/>
										{{ ::profile.answers}}
										<br/>
										{{ ::profile.requests}}
									</p>
								</div>
								<div class="well col-md-6" style="margin-right:5px;height:200px;">
									<div style="display:inline;float:left;max-width:35%">
										<span class="label label-info">#{{userRanking}} Ranked Site-Wide</span>
										<h4 style="margin-top:15%">Reputation</h4>
										<h4 style="display:inline;"><strong>{{profile.points}}</h4></strong> reputation
										<h4>This Week</h4>
										<h4 style="display:inline;">
											<strong>
												<span ng-show="profile.gained_week >= 0">+</span>{{profile.gained_week}}
											</strong>
										</h4> rep. 
										<span class="label label-success" ng-show="profile.gained_week >= 0">+{{profile.this_week}}%</span><span ng-show="profile.gained_week < 0" class="label label-primary">{{profile.this_week}}%</span>
									</div>
									<div ng-if="data && labels" style="display:inline;float:right;width:65%;">
										<canvas id="line" class="chart chart-line" data="data"
										  labels="labels" click="onClick" height="150" style="padding-top:0px;padding-bottom:0px;margin-top:0px;margin-bottom:0px;">
										</canvas> 
									</div>
								</div>
								<div class="col-md-3 well" style="height:200px;">
									<h4>Info</h4>
									<h5>{{ ::profile.location}}</h5>
									<h5>{{ ::profile.last_logged_in}} last logged in</h5>
									<h4>Related</h4>
									<div ng-show="profile.github || profile.stack_exchange">
										<a ng-href="{{profile.github}}">{{ ::profile.github}}</a>
										<a ng-href="{{profile.stack_exchange}}">{{ ::profile.stack_exchange}}</a>
									</div>
									<div ng-show="!(profile.github || profile.stack_exchange)">
										<h6 style="text-align:center;">Nothing to show</h6>
									</div>
								</div>
								<div class="col-md-10" style="padding-left:0px">
									<div ng-show="profile.description">
										{{profile.description | limitTo:num}}
										<a href ng-click="num = profile.description.length" ng-show="num == 1000">Read More</a>
										<a href ng-show="num == profile.description.length" ng-click="num = 1000">Read Less</a>
									</div>
									<div ng-show="!profile.description">
										<p class="text-center">
											<h6 style="text-align:center;">Nothing to show here</h6>
										</p>
									</div>
								</div>
							</div>
						</div>

					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-12">
									<h5><strong>Comments</strong></h5>
								</div>
							</div>
							<div ng-repeat="comment in profile.comments" class="commentBlock">
								<div class="row">
									<div class="col-md-12">
										<a ng-href="profile.php?id={{comment.author.user_id}}" style="display:inline;"><img ng-src="{{comment.author.avatar}}" height="30" style="margin-right:5px;">{{comment.author.username}}</a>
										<span style="float:right;">{{comment.date_posted}}</span>
										<hr style="margin-top:5px;padding:0px;margin-bottom:5px;">
									</div>
									<div class="col-md-12" ng-show="comment.editBox == null">
										{{comment.comment_text}}
									</div>
									<div class="col-md-12" ng-show="comment.editBox != null">
										<textarea ng-model="comment.editBox" class="form-control">
										</textarea>
										<button class="btn btn-primary btn-sm" ng-click="editComment(comment)">Submit</button>
									</div>
									<div class="col-md-12">
										<ul class="list-inline">
											<li ng-if="'<?php echo $user_id;?>' == comment.author.user_id">
												<a href ng-click="comment.editBox = comment.comment_text" ng-show="comment.editBox == null">Edit
												</a>
												<a href ng-click="comment.editBox = null" ng-show="comment.editBox != null">Cancel
												</a>
											</li>
											<li ng-if="('<?php echo $user_id;?>' == profileID) || ('<?php echo $user_id;?>' == comment.author.user_id)">
											<a href ng-click="deleteComment(comment.comment_id)">Delete</a>
										</ul>
									</div>
								</div>
							</div>
							<div ng-show="profile.comments.length == 0">
								<h6 style="text-align:center;">No comments to show</h6>
							</div>
							<form>						
								<textarea ng-model="data.commentBox" class="form-control" required minlength="10">
								</textarea>
								<input type="submit" class="btn btn-primary btn-sm" value="Submit" ng-click="postComment()">
							</form>	
						</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-12">
									<h5><strong>Reputation Changes</strong></h5>
								</div>
							</div>
							<div class="row" ng-repeat="date in userRepActivityDates ">
								<div ng-class="getClass($last)">
									<strong>
										{{date}}
									</strong>
									<div ng-repeat="repChange in userRepActivity[date]">
										<div class="row" ng-if="repChange.type == 5">
											<div class="col-md-2 col-md-offset-1">
												<strong style="color:green;" ng-show="repChange.change >= 0">
													+{{repChange.change}}
												</strong>
												<strong style="color:red;" ng-show="repChange.change < 0">
													{{repChange.change}}
												</strong>
											</div>
											<div class="col-md-9">
												<a ng-href="function.php?id={{repChange.linked_id}}">Added {{repChange.message}}</a>
											</div>
										</div>
										<div class="row" ng-if="(repChange.type >= 1) && (repChange.type <= 4)">
											<div class="col-md-2 col-md-offset-1">
												<strong style="color:green;" ng-show="repChange.change >= 0">
													+{{repChange.change}}
												</strong>
												<strong style="color:red;" ng-show="repChange.change < 0">
													{{repChange.change}}
												</strong>
											</div>
											<div class="col-md-9"> 
												<a ng-href="translate.php?ffi={{repChange.result_spec[0]}}&lan={{repChange.result_spec[1]}}">{{repChange.message}} answer/note
													<span ng-show="change.type == 1">
													upvoted
													</span>
													<span ng-show="change.type == 2">
													downvoted
													</span>
													<span ng-show="change.type == 3">
													un-upvoted
													</span>
													<span ng-show="change.type == 4">
													un-downvoted
													</span>	
												</a>
											</div>
										</div>
										<div class="row" ng-if="(repChange.type == 15) || (repChange.type == 16)">
											<div class="col-md-2 col-md-offset-1">
												<strong style="color:green;" ng-show="repChange.change >= 0">
													+{{repChange.change}}
												</strong>
												<strong style="color:red;" ng-show="repChange.change < 0">
													{{repChange.change}}
												</strong>
											</div>
											<div class="col-md-9">
												<a ng-href="translate.php?ffi={{repChange.result_spec[0]}}&lan={{repChange.result_spec[1]}}" ng-if="(repChange.type == 15) || (repChange.type == 16)">
													{{repChange.message}} question <span ng-show="repChange.type == 16">un</span>upvoted
												</a>
											</div>
										</div>
									</div>
								</div>
							</div>
							<p class="text-right">
								<a ng-href="profile.php?id={{profileID}}&tab=rep">View More</a>
							</p>
							<div ng-show="userRepActivity.length == 0">
								<p class="text-center">
									<h6 style="text-align:center;">Nothing to show here</h6>
								</p>
							</div>
							<hr style="padding:0px;margin-top:8px;margin-bottom:3px;">
							<div class="row">
								<div class="col-md-12">
									<h5><strong>Favorite Languages</strong></h5>
								</div>
							</div>
							<div class="row" ng-repeat="tag in user_tags">
								<div ng-class="getClass($last)">
									<span ng-class="tag.class" style="display:inline;">{{tag.language.language_name}}</span>
									<span style="float:right;">{{tag.answers}} answer</span>
								</div>
							</div>
							<div class="row" ng-show="!user_tags.length">
								<div class="col-md-12">
									<p class="text-center">
										<h6 style="text-align:center;">Nothing to show here, no answers yet</h6>
									</p>
								</div>
							</div>
							<hr style="padding:0px;margin-top:3px;margin-bottom:3px;">
							<div class="row">
								<div class="col-md-12">
									<h5><strong>Top Links</strong></h5>
								</div>
							</div>
							<div class="row" ng-repeat="link in userLinks">
								<div ng-class="getClass($last)">
									<span ng-class="link.from_language.class">{{link.from_language.language_name}}</span>
									<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
									<span ng-class="link.to_language.class">{{link.to_language.language_name}}</span>
									<span style="float:right;">{{link.answers}} answers</span>
								</div>
							</div>
							<div class="row" ng-show="!userLinks.length">
								<div class="col-md-12">
									<p class="text-center">
										<h6 style="text-align:center;">Nothing to show here, no answers yet</h6>
									</p>
								</div>
							</div>
							<hr style="padding:0px;margin-top:8px;margin-bottom:3px;">
							<div class="row">
								<div class="col-md-12">
									<h5><strong>Overview</strong></h5>
								</div>
							</div>
							<tabset>
								<tab>
									<tab-heading>
										Answers <span class="badge">{{submittedTranslations.length}}</span>
									</tab-heading>
									<div ng-repeat="translation in submittedTranslations">
										<div class="row">
											<div class="col-md-2">
												<h4 style="text-align:center;">
													{{translation.upvotes}}
												</h4>
												<p style="text-align:center;">
													Upvotes
												</p>
											</div>
											<div class="col-md-10 answerBlock">
												<p>
													<h5><a ng-href="translate.php?ffi={{translation.from_function.function_id}}&lan={{translation.to_language.language_id}}">{{translation.from_function.function_language.language_name}}'s {{translation.from_function.function_name}} equivalent in {{translation.to_language.language_name}}</h5></a>
													{{translation.comment}}
												</p>
											</div>
										</div>
									</div>
								</tab>
								<tab>
									<tab-heading>
										Questions <span class="badge">{{requests.length}}</span>
									</tab-heading>
									<div class="row">
										<div ng-repeat="request in requests">
											<div class="col-md-2">
												<h4 style="text-align:center;">
													{{request.upvotes}}
												</h4>
												<p style="text-align:center;">
													Upvotes
												</p>
											</div>
											<div class="col-md-8">
												<h5><a ng-href="translate.php?src={{request.from_function.function_name}}&lan1={{request.from_function.function_language.language_id}}&lan2={{request.to_language.language_id}}">{{request.from_function.function_language.language_name}}'s {{request.from_function.function_name}} equivalent in {{request.to_language.language_name}}</h5></a>
	<!-- 											{{request.from_function.description | limitTo:100}}... -->
											</div>
	<!-- 										<div class="col-md-1" style="margin-bottom:0px;">
												<h4 style="text-align:center;">
													{{request.answers}}
												</h4>
												Answers
											</div> -->
											<div class="col-md-2" style="margin-bottom:0px;">
												<h4 style="text-align:center;">
													{{request.views}}
												</h4>
												<p style="text-align:center;">
													Views
												</p>
											</div>
<!-- 											<div class="col-md-3">
												<h4 style="text-align:center;">
													{{request.date_requested}}
												</h4>
												<p style="text-align:center;">
													requested ago
												</p>
											</div> -->
											<div class="col-md-12" style="margin-top:0px;">
												<hr style="margin-top:5px;margin-bottom:5px;">
											</div>
										</div>
									</div>
								</tab>
								<tab>
									<tab-heading>
										Contributed Functions <span class="badge">{{submittedFunctions.length}}</span>
								</tab-heading>
								<table class="table table-striped table-hover ">
									<thead>
										<th>Index</th>
										<th>Function Name</th>
										<th>Language</th>
									</thead>
									<tbody>
										<tr ng-repeat="function in submittedFunctions | limitTo:20" ng-class="function.class">
											<td>{{$index + 1}}</td>
											<td>{{function.function_name}}</td>
											<td>{{function.function_language.language_name}}</td>
										</tr>
									</tbody>
								</table>
							</tab>
	<!-- 						<tab heading="Authored" class="pull-right">
								<h4 style="text-align:center;">What could this tab mean? Only time will tell</h4>
							</tab> -->
						</tabset>
			<!-- 				<a href ng-click="showID = 1" class="profileBar">Translations</a>
						<a href ng-click="showID = 2" class="profileBar">Contributed Functions</a>
						<a href ng-click="showID = 3">Submitted Functions</a> -->
					</div>
				</div>
			</div>
		</body>
	</div>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/chartjs/Chart.min.js"></script>
<script src="bower_components\angular-chart.js\dist\angular-chart.js"></script>
<script src="dependencies/highlightjs/highlight.pack.js"></script>
<script src="dependencies/angular-highlight/angular-highlightjs.min.js"></script>
<script src="angular/profile/profile.js"></script>