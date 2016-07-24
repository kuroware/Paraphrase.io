var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap']).config(function($provide) {
	$provide.constant('attemptLogin', 'postRequests/login/login.php');
	$provide.constant('checkUsernameExistence', 'postRequests/login/checkUsernameExistence.php');
	$provide.constant('defaultInput', 'form-group');
	$provide.constant('successInput', 'form-group has-success');
	$provide.constant('warningInput', 'form-group has-warning');
	$provide.constant('registerUser', 'postRequests/register/register.php');
});

app.controller('main', ['$scope', '$http', '$window', 'attemptLogin', function($scope, $http, $window, attemptLogin){
	$scope.login = {}; //To set the holding object for the login credentials since tabset has its own primitive scope

	$scope.init = function(userID, tab) {
		if (userID != 'None') {
			$window.location.href = '/codeTranslator';
			console.log(userID);
		}
		$scope.tab = {};
		if (tab == 'sign') {
			$scope.tab.sign = true;
		}
/*		if (tab == 'log') {
			$scope.tab.log = true;
		}
		else if (tab == 'sign') {
			$scope.tab.sign = true;
		}*/

	}

	$scope.login = function() {
		if ($scope.login.username && $scope.login.password) {
			var promise = $http({
				method: "post",
				url: attemptLogin,
				data: {
					username: $scope.login.username,
					password: $scope.login.password
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$window.location.href = '/codeTranslator'; //Successful login
			}, function(errorResponse) {
				//alert('Error fetching');
				$scope.errorMessage = 'There was an error logging you in with your username and password combination';
			});
		}
	}
}]);

app.controller('register', ['$scope', '$http', 'checkUsernameExistence', 'successInput', 'warningInput', 'defaultInput', 'registerUser', '$window', function($scope, $http, checkUsernameExistence, successInput, warningInput, defaultInput, registerUser, $window){
	$scope.register = {}; //Holding object for the register details	

	$scope.register.usernameClass = 'form-group';
	$scope.register.passwordClass = 'form-group';
	$scope.register.confirmPasswordClass = 'form-group';

	$scope.$watch('register.username', function(newValue, oldValue) {
		if ($scope.register.initialUsername) {
			console.log('checked)');
			if (newValue && newValue.length > 4) {
				//Check if the username is taken
				var promiseCheckUsernameExistence = $http({
					method: "post",
					url: checkUsernameExistence,
					data: {
						username: $scope.register.username
					},
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				}).then(function(successResponse) {
					$scope.register.usernameErrorMessage = '';
					$scope.register.usernameClass = successInput;
				}, function(errorResponse) {	
					$scope.register.usernameErrorMessage = 'This username already exists';
					$scope.register.usernameClass = warningInput;
				})
			}
			else {
				$scope.register.usernameErrorMessage = 'Your username must be at least 5 characters long';
				$scope.register.usernameClass = warningInput;
			}
		}
		else {
			$scope.register.usernameClass = 'form-group';
		}
	});

	$scope.$watch('register.password', function(newValue, oldValue) {
		if ($scope.register.initialPassword) {
			if (!(newValue && newValue.length > 4)) {
				$scope.register.passwordErrorMessage = 'Your password must be at least 5 characters long';
				$scope.register.passwordClass = warningInput;
			}
			else {
				$scope.register.passwordClass = successInput;
			}
		}
		else {
			$scope.register.passwordClass = 'form-group';
		}
	});

	$scope.$watch('register.confirmPassword', function(newValue, oldValue) {
		if ($scope.register.initialConfirm) {
			if (!(newValue && newValue == $scope.register.password)) {
				$scope.register.confirmPasswordErrorMessage = 'Your passwords must match';
				$scope.register.confirmPasswordClass = warningInput;
			}
			else {
				$scope.register.confirmPasswordClass = successInput;
			}
		}
		else {
			$scope.register.confirmPasswordClass = 'form-group';
		}
	});

	$scope.register = function() {
		if ($scope.register.username && $scope.register.username.length > 4 && $scope.register.password.length > 4 && $scope.register.confirmPassword.length > 4) {
			var promiseRegisterUser = $http({
				method: "post",
				url: registerUser,
				data: {
					username: $scope.register.username,
					password: $scope.register.password,
					email: $scope.register.email
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$window.location.href = 'profile.php?id=' + successResponse.data.user_id;
			}, function(errorResponse) {
				alert('Error registering user. This is the error exception thrown: ' + errorResponse.data);
			});
		}

	}
}]);