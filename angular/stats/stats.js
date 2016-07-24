var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', "chart.js"]).config(['$provide', 'ChartJsProvider', function($provide, ChartJsProvider){
	$provide.constant('getStats', 'postRequests/index/getStats.php');
	$provide.constant('getTopUsers', 'postRequests/stats/getAllUserLeaderboard.php');
	$provide.constant('getLanguageStats', 'postRequests/index/getLanguageStats.php');
    // Configure all charts
    ChartJsProvider.setOptions('Line', {
    	bezierCurve : false,
    	datasetFill : false
    });
}]);

app.controller('main', ['$scope', '$http', 'getStats', 'getTopUsers', 'getLanguageStats', function($scope, $http, getStats, getTopUsers, getLanguageStats){

	$scope.init = function(tab) {

		$scope.tabs = [
			'users', 'stats'
		];

		$scope.tab = {};

		for (var i = 0; i < $scope.tabs.length; i++) {
			if ($scope.tabs[i] != tab) {
				$scope.tab[$scope.tabs[i]] = false;
			}
			else {
				$scope.tab[$scope.tabs[i]] = true;
			}
		}
	}

	$scope.select = function(tab_name) {

		if (!$scope.tabs) {
			$scope.tabs = [
				'users', 'stats'
			];
		}

		if (!$scope.tab) {
			$scope.tab = {};
		}

		$scope.showGraphData = true;
	}
	$scope.deselect = function(tab_name) {
		$scope.showGraphData = false;
	}	
	$scope.graphData = {}; //Holding variable for the graph data
	var promiseGetLanguageStats = $http({
		method: "post",
		url: getLanguageStats,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		console.log(successResponse);
		$scope.graphData.languageData = successResponse.data.data;
		$scope.graphData.series = successResponse.data.series;
		$scope.graphData.labels = successResponse.data.labels;
		console.log($scope.series);
		console.log($scope.labels);
		console.log($scope.languageData);
/*	  $scope.graphData.labels = ["January", "February", "March", "April", "May", "June", "July"];
	  $scope.graphData.series = ['Series A', 'Series B'];
	  $scope.graphData.data = [
	    [65, 59, 80, 81, 56, 55, 40],
	    [28, 48, 40, 19, 86, 27, 90]
	  ];*/
	}, function(errorResponse){
		alert('Error fetching statistics for languages');
	});

	var promiseGetTopUsers = $http({
		method: "post",
		url: getTopUsers,
		data: {
			filter: 'all'
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.topUsers = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		console.log(errorResponse);
		alert('Error fetching top users');
	});

	var promiseGetStats = $http({
		method: "post",
		url: getStats,
		data: {
			dev: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.stats = successResponse.data;
			$scope.selectedStats = successResponse.data[1];
			$scope.showStats = true;
	}, function(errorResponse) {
		alert('Error fetching stats');
		$scope.showStats = false;
	});
}]);