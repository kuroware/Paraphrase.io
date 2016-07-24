var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'angucomplete']).config(function($provide) {
	$provide.constant('getSearchStats', 'postRequests/info/getStatsSearch.php');
	$provide.constant('getLanguages', 'postRequests/index/fetchLanguages.php');
});
app.controller('main', ['$scope', '$http', '$window', 'getSearchStats', 'getLanguages', function($scope, $http, $window, getSearchStats, getLanguages){
	$scope.selectedLanguageID = 1; //Default

	var promiseGetSearchStats = $http({
		method: "post",
		url: getSearchStats,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.info = successResponse.data;
	}, function(errorResponse) {
		alert('Error fetching stats');
	});

	$scope.search = function() {
		$window.location.href = 'search.php?search_phrase=' + $scope.searchPhrase + '&src=' + $scope.selectedLanguageID;
	}

	var promiseGetLanguages = $http({
		method: "post",
		url: getLanguages,
		data: {
			dev: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.languages = successResponse.data;
		console.log($scope.languages);
	}, function(errorResponse) {
		alert('Error fetching translations');
	});
}])