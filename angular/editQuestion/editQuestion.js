var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap']).config(function($provide) {
	$provide.constant('loadQuestion', 'postRequests/question/loadQuestion.php');
	$provide.constant('editQuestion', 'postRequests/editQuestion/editQuestion.php');
	$provide.constant('getLanguages', '/postRequests/index/fetchLanguages.php');

});
app.controller('main', ['$scope', '$http', 'loadQuestion', 'editQuestion', '$window', 'getLanguages', function($scope, $http, loadQuestion, editQuestion, $window, getLanguages){

	var promiseGetLanguages = $http({
		method: "post",
		url: getLanguages,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successReponse) {
		$scope.languages = successReponse.data;
	}, function(errorResponse) {
		alert('Error fetching languages');
	});

	$scope.init = function(questionID) {
		$scope.question = {};
		$scope.question.question_id = questionID;
		var promiseLoadQuestion = $http({
			method: "post",
			url: loadQuestion,
			data: {
				question_id: $scope.question.question_id
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});

		promiseLoadQuestion.then(function(successResponse) {
			for (var attrname in successResponse.data) {
				$scope.question[attrname] = successResponse.data[attrname];
			}
			console.log($scope.question);
		}, function(errorResponse) {
			alert('Error loading question');
		});
	}

	$scope.pushEdits = function() {
		if ($scope.question.title && $scope.question.body) {
			var promisePushEdits = $http({
				method: "post",
				url: editQuestion,
				data: {
					question_id: $scope.question.question_id,
					title: $scope.question.title,
					body: $scope.question.body
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log('Succcessfully editted');
				console.log(successResponse);
			/*	$window.location.href = 'question.php?id=' + $scope.question.question_id;*/
			}, function(errorResponse) {
				alert('Error editting question');
			});
		}
	}
}]);