var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', "chart.js"]).config(['$provide', 'ChartJsProvider', function($provide, ChartJsProvider) {
	$provide.constant('getUserSubmittedFunctions', 'postRequests/user/loadUserSubmittedFunctions.php');
	$provide.constant('getUserTranslations','postRequests/user/loadUserTranslations.php');
	$provide.constant('loadUser', 'postRequests/user/loadUser.php');
	$provide.constant('getUserGraph', 'postRequests/user/getUserRepGraph.php');
	$provide.constant('getUserRequests', 'postRequests/user/loadUserRequests.php');
	$provide.constant('postCommentOnProfile', 'postRequests/user/postCommentonProfile.php');
	$provide.constant('getUserTags', 'postRequests/user/loadUserTags.php');
	$provide.constant('getUserLinks', 'postRequests/user/getUserLinks.php');
	$provide.constant('deleteProfileComment', 'postRequests/user/deleteProfileComment.php');
	$provide.constant('editProfileComment', 'postRequests/user/editProfileComment.php');
	$provide.constant('getUserRepActivity', 'postRequests/user/getUserReputationActivity.php');
	$provide.constant('getAllUserRepActivity', 'postRequests/user/getAllUserReputationActivity.php');
	$provide.constant('getUserRanking', 'postRequests/user/getUserRanking.php');

    ChartJsProvider.setOptions('Line', {
    	bezierCurve : false,
    	datasetFill : true
    });
}]);

app.controller('main', ['$scope', '$http', 'getUserTranslations', 'getUserSubmittedFunctions', 'loadUser', 'getUserGraph', 'getUserRequests', 'postCommentOnProfile', 'getUserTags', '$q', 'getUserLinks', 'deleteProfileComment', 'editProfileComment', 'getUserRepActivity', 'getAllUserRepActivity', 'getUserRanking', function($scope, $http, getUserTranslations, getUserSubmittedFunctions, loadUser, getUserGraph, getUserRequests, postCommentOnProfile, getUserTags, $q, getUserLinks, deleteProfileComment, editProfileComment, getUserRepActivity, getAllUserRepActivity, getUserRanking){

	$scope.data = {};

	$scope.data.currentPage = 1,
	$scope.data.numPerPage = 10,
	$scope.data.filteredUserRepChanges = [],
	$scope.data.userRepChanges = [],
	$scope.data.maxSize = 5;

	$scope.deleteComment = function(comment_id) {
		var promiseDeleteComment = $http({
			method: "post",
			url: deleteProfileComment,
			data: {
				comment_id: comment_id
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			for (var i = 0; i < $scope.profile.comments.length; i++) {
				if ($scope.profile.comments[i].comment_id == comment_id) {
					$scope.profile.comments.splice(i, 1);
					break;
				}
			}
		}, function(errorResponse) {
			alert('Error deleting comment');
		});
	}

	$scope.editComment = function(comment) {
		if (!comment.editBox) {
			$scope.deleteComment(comment.comment_id);
		}
		else {
			if (comment.editBox && comment.editBox != comment.comment_text) {
				var promiseEditComment = $http({
					method: "post",
					url: editProfileComment,
					data: {
						comment_id: comment.comment_id,
						edit_text: comment.editBox
					},
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				}).then(function(successResponse) {
					for (var i = 0; i < $scope.profile.comments.length; i++) {
						if ($scope.profile.comments[i].comment_id == comment.comment_id) {
							$scope.profile.comments[i].comment_text = comment.editBox;
							$scope.profile.comments[i].editBox = null;
							break;
						}
					}
				}, function(errorResponse) {
					alert('Error deleting comment');
				});
			}
		}
	}

	$scope.num = 1000;

	$scope.getClass = function(last) {
		if (last) {
			return 'col-md-12';
		}
		else {
			return 'col-md-12 userTag';
		}
	}

	$scope.postComment = function() {
		if ($scope.data.commentBox && $scope.data.commentBox.length > 10) {
			var promisePostComment = $http({
				method: "post",
				url: postCommentOnProfile,
				data: {
					profile_id: $scope.profileID,
					comment_text: $scope.data.commentBox
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				//$scope.data.commentBox = '';
/*				var newComment = {};
				newComment.comment_text = $scope.data.commentBox;
				newComment.author = {};
				newComment.author.user_id = $scope.userID;
				newComment.author.username = $scope.username;*/
				/*$scope.commments.push(successResponse.data);*/

				//Push the comnent into the array
				$scope.profile.comments.unshift(successResponse.data);
			}, function(errorResponse) {
				alert('Error posting comment');
			});
		}
	}

	$scope.getGraph = function() {

		$scope.data.tab = 'Reputation';
		
		//Function to get the required information for loading the reputation tab

		var promiseGetUserGraph = $http({
			method: "post",
			url: getUserGraph,
			data: {
				user_id: $scope.profileID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.graphData = successResponse.data;
			$scope.data = Array.prototype.slice.call($scope.graphData[1]);
			for (var i = 0; i < $scope.data.length; i++) {
				$scope.data[i] = parseInt($scope.data[i]);
			}
			$scope.data = [$scope.data];
			console.log($scope.data);
			$scope.labels = Array.prototype.slice.call($scope.graphData[0]);
			console.log($scope.labels);
		}, function(errorResponse) {
			alert('Error fetching profile graph');
		});
	}

	$scope.getPaginationReputation = function() {
		var promiseGetUserRepActivity = $http({
			method: "post",
			url: getAllUserRepActivity,
			data: {
				user_id: $scope.profileID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			console.log(successResponse);
			$scope.data.currentPage = 1,
			$scope.data.numPerPage = 10,
			$scope.data.filteredUserRepChanges = [],
			$scope.data.userRepChanges = [],
			$scope.data.maxSize = 5;
			$scope.userRepChanges = successResponse.data;
/*			$scope.data.currentPage = 1;
			$scope.data.numPerPage = 10;
		 	var begin = (($scope.data.currentPage - 1) * $scope.data.numPerPage)
		    , end = begin + $scope.data.numPerPage;
		    console.log(begin);
		    console.log(end);*/
		 /*   $scope.data.filteredUserRepChanges = $scope.userRepChanges.slice(begin, end);*/
		    console.log($scope.data.filteredUserRepChanges);
		}, function(errorResponse) {
			console.log(errorResponse);
			alert('Error fetching rep activity');
		});

		return promiseGetUserRepActivity;
	}

	$scope.series = ["dummy"];

	$scope.init = function(profileID, userID) {
		var deferred = $q.defer();

		$scope.profileID = profileID;
		$scope.userID = userID;

		var promiseFetchUserRanking = $http({
			method: "post",
			url: getUserRanking,
			data: {
				user_id: $scope.profileID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			console.log(successResponse);
			$scope.userRanking = successResponse.data.ranking;
		}, function(errorResponse) {
			alert('Error fetching ranking');
		})

		deferred.resolve();
		$scope.deferred = deferred.promise; //Resolve the promise
	}

	$scope.getAnswers = function() {
		$scope.deferred.then(function(successResponse) {
			var promiseGetUserTranslations = $http({
				method: "post",
				url: getUserTranslations,
				data: {
					user_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.submittedTranslations = successResponse.data;
				console.log(successResponse.data);
			}, function(errorResponse) {
				$scope.submittedTranslations = [];
				alert('Error fetching profile submitted translations');
			});
		}, function(errorResponse) {
			alert('Error fetching questions');
		})
	}
	$scope.getQuestions = function() {
		var promiseGetUserRequests = $http({
			method: "post",
			url: getUserRequests,
			data: {
				profile_id: $scope.profileID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.requests = successResponse.data;
			console.log($scope.requests);
		}, function(errorResponse) {
			$scope.requests = [];
			alert('Error fetching profile for requests');
		});
	}

	$scope.getSummary = function() {
		$scope.deferred.then(function(successResponse) {

			var promiseGetUserRepActivity = $http({
				method: "post",
				url: getUserRepActivity,
				data: {
					user_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				var userRepActivity = successResponse.data;
				$scope.userRepActivity = successResponse.data;
				$scope.userRepActivityDates = []; //Array to store the dates possible

				//Reverse the order of the object since we need it to be in correct date order
				angular.forEach(userRepActivity, function(value, key) {
					this.push(key);
				}, $scope.userRepActivityDates);
			}, function(errorResponse) {
				console.log('error');
				alert('Error fetching rep activity');
				console.log(errorResponse);
			})

			var promiseGetUserLinks = $http({
				method: "post",
				url: getUserLinks,
				data: {
					profile_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.userLinks = successResponse.data;
			}, function(errorResponse) {
				alert('Error fetching links');
			})

			var promiseGetUserRequests = $http({
				method: "post",
				url: getUserRequests,
				data: {
					profile_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.requests = successResponse.data;
				console.log($scope.requests);
			}, function(errorResponse) {
				$scope.requests = [];
				alert('Error fetching profile for requests');
			});

			var promiseGetUserTranslations = $http({
				method: "post",
				url: getUserTranslations,
				data: {
					user_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.submittedTranslations = successResponse.data;
				console.log(successResponse.data);
			}, function(errorResponse) {
				$scope.submittedTranslations = [];
				alert('Error fetching profile submitted translations');
			});

			var promiseGetUserGraph = $http({
				method: "post",
				url: getUserGraph,
				data: {
					user_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.graphData = successResponse.data;
				$scope.data = Array.prototype.slice.call($scope.graphData[1]);
				for (var i = 0; i < $scope.data.length; i++) {
					$scope.data[i] = parseInt($scope.data[i]);
				}
				$scope.data = [$scope.data];
				console.log($scope.data);
				$scope.labels = Array.prototype.slice.call($scope.graphData[0]);
				console.log($scope.labels);
			}, function(errorResponse) {
				alert('Error fetching profile graph');
			});

			var promiseFetchSubmitted = $http({
				method: "post",
				url: getUserSubmittedFunctions,
				data: {
					user_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$scope.submittedFunctions = successResponse.data;
			}, function(errorResponse) {
				$scope.submittedTranslations = [];
				alert('Error fetching profile submitted translations');
			});

			var promiseGetUser = $http({
				method: "post",
				url: loadUser,
				data: {
					user_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$scope.profile = successResponse.data;
				$scope.profile.gained_week = parseInt($scope.profile.gained_week);
			}, function(errorResponse) {
				$scope.profile = null;
				alert('Error fetching profile');
			});

			var promiseGetUserTags = $http({
				method: "post",
				url: getUserTags,
				data: {
					profile_id: $scope.profileID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.user_tags = successResponse.data;
			}, function(errorResponse) {
				alert('Error fetching tags');
			})

		}, function(errorResponse) {
			alert('Error');
		});
	}

	$scope.$watch('data.currentPage + data.numPerPage', function() {
		/*if ($scope.data.tab == 'Reputation') {*/
			if (!$scope.getRepPagination) {
				$scope.getRepPagination = $scope.getPaginationReputation();
			}
			$scope.getRepPagination.then(function(successResponse) {
			 	var begin = (($scope.data.currentPage - 1) * $scope.data.numPerPage)
			    , end = begin + $scope.data.numPerPage;
			    console.log(begin);
			    console.log(end);
			    $scope.data.filteredUserRepChanges = $scope.userRepChanges.slice(begin, end);
			    console.log($scope.data.filteredUserRepChanges);
			}, function(errorResponse) {
				alert('Erorr');
			});
		//}
	});
}]);