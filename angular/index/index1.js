var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'angucomplete', "ng-fusioncharts"]).config(function($provide){
	$provide.constant('fetchLanguages', 'postRequests/index/fetchLanguages.php');
	$provide.constant('translateURL', 'translate.php?id=');
	$provide.constant('autosuggestLanguage', 'postRequests/index/searchFunctionToLanguage.php');
	$provide.constant('getStats', 'postRequests/index/getStats.php');
	$provide.constant('getTopUsers', 'postRequests/index/loadTopUsers.php');

});

app.controller('main', ['$scope', '$http', '$window', 'fetchLanguages', 'autosuggestLanguage', 'getStats', 'getTopUsers', function($scope, $http, $window, fetchLanguages, autosuggestLanguage, getStats, getTopUsers){
	$scope.default = 2;
	$scope.lan2 = 2;
	$scope.selectedLanguage;

	$scope.selectAngu = function() {
		$scope.display = true;
	}

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
	var promise = $http({
		method: "post",
		url: fetchLanguages,
		data: {
			dev: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.languages = successResponse.data;
	}, function(errorResponse) {
		$scope.languages = [];
		alert('Error fetching languages');
	});

	$scope.translate = function() {
		if (!$scope.code1.originalObject && $scope.searchPhrase) {
			$window.location.href = 'search.php?search_phrase=' + $scope.searchPhrase + '&src=' + $scope.lan2; 
		}
		console.log($scope.code1);
		$scope.lan1 = $scope.code1.originalObject.function_language.language_id;
		console.log($scope.lan2);
		$scope.code1 = $scope.code1.originalObject.function_name;
		console.log($scope.code1);
		if ($scope.code1 && $scope.lan2 && $scope.lan1) {
			promise.then(function(successResponse) {
				$window.location.href = 'translate.php?src=' + $scope.code1 + '&lan1=' + $scope.lan1 + '&lan2='+ $scope.lan2
			}, function(errorResponse) {
				alert('Error translating');
			});
		}
	}

	$scope.$watch('code1', function(v1, v2) {
		if (v1 && v1.length >= 3) {
			var promise = $http({
				method: "post",
				url: autosuggestLanguage,
				data: {
					some_function: v1
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				if (successResponse.data[0]) {
					$scope.autoLanguage = successResponse.data[0];
					$scope.lan1 = $scope.autoLanguage.language_id;
				}

				else {
					var Language = function() {
						this.language_id = null;
						this.language_name = 'No language'
						this.function_id = null;
					};
					$scope.autoLanguage = new Language();
					$scope.lan1 = $scope.autoLanguage.language_id;
					console.log($scope.autoLanguage);
				}
			}, function(errorResponse) {
				console.log(errorResponse);
				$scope.autoLanguage = null;
				alert('Error fetching auto language suggest');
			});
		}
		else if (!v1) {
			$scope.autoLanguage = null;
		}
	})
	
}])