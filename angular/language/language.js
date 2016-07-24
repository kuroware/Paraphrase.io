var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'ngSanitize']).config(function($provide) {
	$provide.constant('getLanguageFunctions', 'postRequests/language/getLanguageFunctions.php');
	$provide.constant('getLanguage', 'postRequests/info/getLanguage.php');

});

app.filter('search', function() {
	return function(arrayToSearch, searchPhrase) {
		var returnArray = [];
		if (!searchPhrase) {
			var returnArray = arrayToSearch;
		}
		else {
			if (arrayToSearch && Array.isArray(arrayToSearch)) {
				var strLength = searchPhrase.length;
				for (var i = 0; i < arrayToSearch.length; i++) {
					if (arrayToSearch[i].function_name.substring(0, strLength) == searchPhrase) {
						returnArray.push(arrayToSearch[i]);
					}
				}
			}
		}
		return returnArray;
	}
})

app.controller('main', ['$scope', '$http', 'getLanguageFunctions', 'getLanguage', 'searchFilter', function($scope, $http, getLanguageFunctions, getLanguage, searchFilter){


	$scope.data = {},
	$scope.data.currentPage = 1,
	$scope.data.numPerPage = 20,
	$scope.data.maxSize = 10,
	$scope.filteredFunctions = [];

	$scope.$watch('data.search', function(newvValue, oldValue) {
		if ($scope.data.filteredFunctions) {
			$scope.data.filteredFunctions = searchFilter($scope.data.functions, newvValue);
		}
	});

	$scope.$watch("data.currentPage + data.numPerPage", function() {
		//Fetch the inital functions through here with promise
		if (!$scope.data.functions) {
			$scope.getFunctionsPromise = $scope.getFunctions();
		}
		$scope.getFunctionsPromise.then(function(successResponse) {
			var begin = (($scope.data.currentPage - 1) * $scope.data.numPerPage)
			, end = begin + $scope.data.numPerPage;

			console.log(begin + 'and' + end);

			$scope.data.filteredFunctions = $scope.data.functions.slice(begin, end);
			console.log($scope.data.filteredFunctions);
		});
	});

	$scope.getFunctions = function() {
		var promiseGetFuncions = $http({
			method: "post",
			url: getLanguageFunctions,
			data: {
				language_id: $scope.languageID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			console.log(successResponse);
			$scope.data.functions = successResponse.data;
			console.log($scope.data.functions.length);
		}, function(errorResponse) {
			//alert('Error fetching functions!');
		});
		return promiseGetFuncions;
	}

	$scope.init = function(languageID) {
		$scope.languageID = languageID;
		var promise = $http({
			method: "post",
			url: getLanguage,
			data: {
				language_id: $scope.languageID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			console.log(successResponse);
			$scope.language = successResponse.data;
		}, function(errorResponse) {
			alert('Error fetching language');
		});
	}	
}]);