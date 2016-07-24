var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'ngDialog']).config(function($provide) {
	$provide.constant('loadUser', 'postRequests/user/loadUser.php');
	$provide.constant('editUser', 'postRequests/user/updateUserProfile.php');
	$provide.constant('editUserLinks', 'postRequests/user/editUserLinks.php');
});
app.controller('main', ['$scope', '$http', 'loadUser', 'editUser', 'editUserLinks', function($scope, $http, loadUser, editUser, editUserLinks){
	$scope.init = function(profileID, errorID) {
		$scope.profileID = profileID;
		$scope.errorID = errorID;
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
		}, function(errorResponse) {
			$scope.profile = null;
			alert('Error fetching profile');
		});
	}

	$scope.editUser = function() {
		var promiseGetUser = $http({
			method: "post",
			url: editUser,
			data: {
				email: $scope.profile.email,
				description: $scope.profile.description,
				location: $scope.profile.location
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			alert('Updated');
		}, function(errorResponse) {
			alert('Error updating profile');
		});
	}

	$scope.editUserLinks = function() {
		var promiseGetUser = $http({
			method: "post",
			url: editUserLinks,
			data: {
				github: $scope.profile.github,
				stackexchange: $scope.profile.stack_exchange
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			alert('Updated');
		}, function(errorResponse) {
			alert('Error updating profile');
		});

	}



	
}])