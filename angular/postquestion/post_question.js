var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'angucomplete-alt']).config(function($provide) {
	$provide.constant('postQuestion', '/postRequests/postQuestion/post_question.php');
	$provide.constant('getLanguages', '/postRequests/index/fetchLanguages.php');
});

app.controller('main', ['$scope', '$http', 'postQuestion', 'getLanguages', '$window', function($scope, $http, postQuestion, getLanguages, $window){

	$scope.selectedSourceLanguageID = 1; //By default
	$scope.selectedDestinationLanguageID = 2; //By default
	$scope.taggedLanguageID = null; //By default;

	$scope.question = {}; //Object to hold the question parameters

	$scope.postQuestion = function() {
		if ($scope.question.title && $scope.question.body && $scope.selectedSourceLanguageID && $scope.selectedDestinationLanguageID) {
			console.log($scope.selectedDestinationLanguageID);
			console.log($scope.selectedSourceLanguageID);
			console.log($scope.question.title);
			console.log($scope.question.body);
			var promisePostQuestion = $http({
				method: "post",
				url: postQuestion,
				data: {
					title: $scope.question.title,
					body: $scope.question.body,
					src: $scope.selectedSourceLanguageID,
					des: $scope.selectedDestinationLanguageID,
					tagged_language: $scope.taggedLanguageID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			});
			promisePostQuestion.then(function(successResponse) {
				$scope.question.body = '';
				$scope.question.title = '';
				$scope.questionID = successResponse.data.question_id;
				console.log(successResponse);
				if (successResponse.data.question_id) {
					$window.location.href = 'question.php?id=' + $scope.questionID;
				}
				else {
					alert('Error posting your question for some reason');
				}
			}, function(errorResponse) {
				alert('Error posting your question for some reason...');
				console.log(errorResponse);
			});
		}
	}

	$scope.postParaphrase = function() {
		if ($scope.sourceFunction && $scope.selectedDestinationLanguageID) {
			$window.location.href = 'translate.php?src=' + $scope.sourceFunction + '&lan1' + $scope.souceFunction.function_language.language_id + '&lan2=' + $scope.selectedDestinationLanguageID;
		}
	}

	var promiseGetLanguages = $http({
		method: "post",
		url: getLanguages,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successReponse) {
		$scope.languages = successReponse.data;
	}, function(errorResponse) {
		alert('Error fetching languages');
	});
}]);