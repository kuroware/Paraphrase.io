var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap']).config(function($provide) {
	$provide.constant('loadQuestion', 'postRequests/question/loadQuestion.php');
	$provide.constant('upvoteQuestion', 'postRequests/user/upvoteQuestion.php');
	$provide.constant('downvoteQuestion', 'postRequests/user/downvoteQuestion.php');
});

app.controller('main', ['$scope', '$http', 'loadQuestion', 'upvoteQuestion', 'downvoteQuestion', function($scope, $http, loadQuestion, upvoteQuestion, downvoteQuestion){
	$scope.init = function(questionID, userID) {
		$scope.user = {};
		$scope.user.user_id = userID;
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

	$scope.upvote = function() {
		var promiseUpvoteQuestion = $http({
			method: "post",
			url: upvoteQuestion,
			data: {
				question_id: $scope.question.question_id
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.question.upvoted = !$scope.question.upvoted;
			if ($scope.question.downvoted) {
				if ($scope.question.upvoted) {
					$scope.question.downvoted = false;
					$scope.question.downvotes--;
				}
			}
			if ($scope.question.upvoted) {
				$scope.question.upvotes++;
			}
		}, function(errorResponse) {
			alert('Error upvoting');
		});
	}

	$scope.downvote = function() {
		var promiseDownvoteQuestion = $http({
			method: "post",
			url: downvoteQuestion,
			data: {
				question_id: $scope.question.question_id
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.question.downvoted = !$scope.question.downvoted;
			if ($scope.question.upvoted) {
				if ($scope.question.downvoted) {
					$scope.question.upvoted = false;
					$scope.question.upvotes--;
				}
			}
			if (!$scope.downvoted) {
				$scope.question.downvotes++;
			}
		}, function(errorResponse) {
			alert('Error downvoting');
		});
	}

	$scope.report = function() {

	}
	
}]);