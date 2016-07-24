var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'hljs', 'ngDialog', 'angucomplete-alt']).config(function($provide) {
	//Constants here
	$provide.value('constructReasons', function(inputReasons) {
		var htmlString = '';
		if (Array.isArray(inputReasons) && inputReasons.length > 0) {
			var htmlString = '<ul>';
			for (var i = 0; i < inputReasons.length; i++) {
				var liString = '<li>' + inputReasons[i] + '</li>';
				var htmlString = htmlString.concat(liString);
			}
		}
		return htmlString;
	});
	$provide.constant('getLanguages', 'postRequests/index/fetchLanguages.php');
});

app.controller('main', ['$scope', '$http', 'ngDialog', 'constructReasons', 'getLanguages', '$window', function($scope, $http, ngDialog, constructReasons, getLanguages, $window){

	$scope.updateScope = function(str) {
		$scope.alternate = str;
/*		if ($scope.alternate && ($scope.alternate.indexOf(' ') != -1)) {
			console.log('changed');
			$scope.inputType = 'multi';
		}*/
	}

	$scope.questionSearch = function() {
		ngDialog.open({
			template: 'html/ngDialog/search_info.html'
		});
	}

	$scope.data = {};

	$scope.init = function(searchPhrase) {
		$scope.searchPhrase = searchPhrase;
	}

	$scope.changeView = function(i) {
		$scope.data.view = i;
	}
	
	$scope.attemptTranslation = function() {
		console.log($scope.searchPhrase);
		console.log($scope.alternate);
		if ($scope.searchPhrase && $scope.searchPhrase.originalObject) {
			var functionName = $scope.searchPhrase.originalObject.function_name;
			$window.location.href = '/search.php?search_phrase=' + functionName + '&src=' + $scope.selectedLanguageID;
		}
		else {
			$window.location.href = '/search.php?search_phrase=' + $scope.alternate + '&src=' + $scope.selectedLanguageID;
		}
/*		console.log($scope.searchCode);
		console.log($scope.searchPhrase);
		$scope.lan1 = $scope.searchCode.originalObject.function_language.language_id;
		$scope.code1 = $scope.searchCode.originalObject.function_name;
		$scope.lan2 = $scope.selectedLanguageID;

		console.log($scope.lan1);
		console.log($scope.lan2);
		console.log($scope.code1);

		if ($scope.lan2 && $scope.lan1 && $scope.code1) {
			$window.location.href = 'translate.php?src=' + $scope.code1 + '&lan1=' + $scope.lan1 + '&lan2='+ $scope.lan2;
		}*/
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

	$scope.databaseAnswer = function() {
		ngDialog.open({
			template: 'html/ngDialog/database_answer.html'
		});
	}

	$scope.expandReasons = function(reason) {
		var reason = JSON.parse(reason);
		var reason = Array.prototype.slice.call(reason);
		var htmlString = constructReasons(reason);
		ngDialog.open({
			plain: true,
			template: '<html><p><center><h2>Reasons</h2><p><h6>The database creates an answer automatically for functions that can be directly translated without needing additional functions. The accuracy may be volatile (as the database searches against the cateogry of the function placement). The top rated answer is automatically shown (does not have to be a database answer) and if you have a better answer, you may submit it below</h6></p>' + htmlString
		});
	}
	
}]);