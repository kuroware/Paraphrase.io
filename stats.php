<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
if (isset($_GET['tab'])) {
	$tabs = array('users', 'stats');
	if (in_array($_GET['tab'], $tabs)) {
		$tab = $_GET['tab'];
	}
	else {
		$tab = 'users';
	}
}
else {
	$tab = 'users';
}
require_once 'navbar.php';
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
<!-- 		<link rel="stylesheet" type="text/css" href="css/index.css"> -->
		<link rel="stylesheet" type="text/css" href="css/stats.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="dependencies/angular-charts/angular-chart.css">
	</head>
	<body class="container-fluid" ng-controller="main" ng-init="init('<?php echo $tab;?>')">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
<!--			<div class="filters">
				<a href>Week</a>
				<a href>Month</a>
				<a href>Quarter</a>
				<a href>Year</a>
				<a href>All Time</a>
			</div>-->
<!-- 				<div class="jumbotron">
					<p>
						Polyphrase aims for high-quality, well-explained, and succint when necessary equivalences and function/method translations. As such, users with reputation are recognized for their contributions to the community, the site, and to many users who may view it.
					</p>
				</div> -->
				<tabset>
					<tab active="tab.users">
						<tab-heading>
							Users Leaderboard
						</tab-heading>
						<table class="table table-striped table-hover">
							<thead>
								<th>Ranking</th>
								<th>Username</th>
								<th>Reptuation</th>
								<th>Weekly Reputation Change</th>
								<th>Top Language</th>
								<th>Total Answers</th>
								<th>Weekly Rank Change</th>
								<th>Daily Rank Change</th>
							</thead>
							<tbody>
								<tr ng-repeat="user in topUsers">
									<td>
										<img src="ui/medal_1.png" ng-show="$index == 0" height="20">
										<img src="ui/medal_2.png" ng-show="$index == 1" height="20">
										<img src="ui/medal_3.png" ng-show="$index == 2" height="20">
										{{$index + 1}}
									</td>
									<td><a ng-href="profile.php?id={{user.user_id}}">{{user.username}}</a></td>
									<td>
										{{user.points}}&nbsp;<span class="label label-success" ng-show="user.weekly_point_percentage_change >= 0">+{{user.weekly_point_percentage_change}}% from last week</span>
										<span class="label label-success" ng-show="user.weekly_point_percentage_change < 0">-{{user.weekly_point_percentage_change}}% from last week</span>
									</td>
									<td>
									</td>	
									<td ng-if="user.top_category">{{user.top_category}} - {{user.language_answers}} answers</td>
									<td ng-if="!user.top_category">N/A</td>
									<td>
										{{user.total_answers}}
									</td>
									<td style="color:green" ng-show="user.weekly_ranking_change >= 0"><strong>+ {{user.weekly_ranking_change}}</strong></td>
									<td style="color:red" ng-show="user.weekly_ranking_change < 0"><strong>- {{user.weekly_ranking_change}}</strong></td>
									<td>{{user.daily_ranking_change}}</td>
								</tr>
							</tbody>
						</table>
					</tab>
					<tab select="select('stats')" deselect="deselect('stats')" active="tab.stats">
						<tab-heading>
							Language Statistics
						</tab-heading>
					</tab>
<!-- 					<tab>
						<tab-heading>
							Site Statistics
						</tab-heading>
					</tab> -->
				</tabset>
				<div ng-if="graphData.languageData && graphData.labels && graphData.series && showGraphData" class="graphBox">
					<h4 style="text-align:center;">Indexed Functions by Language</h4>
					<canvas id="line" class="chart chart-line" data="graphData.languageData[0]"
					  labels="graphData.labels" series="graphData.series" click="onClick" height="100" legend="true">
					</canvas>
					<h4 style="text-align:center;">Language Popularity - By Number of Translations At 'X' Language</h4>
					<canvas id="line" class="chart chart-line" data="graphData.languageData[1]"
					  labels="graphData.labels" series="graphData.series" click="onClick" height="100" legend="true">
					</canvas>
				</div>
			</div>
		</div>
	</body>
</html>
<script src="bower_components/angular/angular.min.js"></script>
<script src="dependencies/chartjs/Chart.min.js"></script>
<script src="bower_components\angular-chart.js\dist\angular-chart.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="angular/stats/stats.js"></script>