var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'angucomplete', 'ngDialog']).config(function($provide) {
	$provide.constant('addFunction', 'postRequests/addFunction/addFunction.php');
	$provide.constant('fetchLanguages', 'postRequests/index/fetchLanguages.php');
	$provide.constant('getCategories', 'postRequests/info/getCategories.php');
	$provide.constant('getSuperCategories', 'postRequests/info/getSuperCategories.php');
});

app.controller('main', ['$scope', '$http', 'addFunction', 'fetchLanguages', 'getCategories', 'getSuperCategories', 'ngDialog', function($scope, $http, addFunction, fetchLanguages, getCategories, getSuperCategories, ngDialog){
	
	$scope.selectedLanguage = '2';
	$scope.selectedSuper = '2';

	
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
		alert('Error loading');
	});

	$scope.$watch('selectedSuper', function(v1, v2) {
		if (v1 && parseInt(v1) != 'NaN') {
			var promiseCat = $http({
				method: "post",
				url: getCategories,
				data: {
					type: v1
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.categories = successResponse.data;
			}, function(errorResponse) {
				alert('Error loading');
			});
		}
	});

	var promiseSuperCat = $http({
		method: "post",
		url: getSuperCategories,
		data: {
			dev: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.superCategories = successResponse.data;
	}, function(errorResponse) {
		alert('Error loading');
	});

	$scope.submitFunction = function() {
		if ($scope.selectedSuper && $scope.selectedLanguage && $scope.functionName) {
			if ($scope.selectedSuper) {
				$scope.identicalfunction = null;
			}
			else if ($scope.selectedSuper.originalObject && $scope.selectedSuper.originalObject.category_id) {
				$scope.selectedSuper = $scope.identicalfunction.originalObject.category_id;
			}
			else {
				$scope.selectedSuper = 0;
			}
			var promise = $http({
				method: "post",
				url: addFunction,
				data: {
					language_id: $scope.selectedLanguage,
					syntax: $scope.syntax,
					summary: $scope.summary,
					link: $scope.link,
					function_name: $scope.functionName,
					category_id: $scope.selectedSuper,
					super_id: $scope.selectedSuper
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.submitted = true;
				ngDialog.open({
					template: 'html/ngDialog/success_submit.html'
				});
			}, function(errorResponse) {
				alert('Error posting function');
			})
		}
	}
}])