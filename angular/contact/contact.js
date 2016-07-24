angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'ngDialog'])
	.config(['$provide', function($provide) {
		$provide.constant('contactPHP', 'postRequests/contact/mailFeedback.php');
	}])
	.controller('controller', ['$scope', '$http', 'contactPHP', 'ngDialog', '$window', function($scope, $http, contactPHP, ngDialog, $window){
		//Form attributes
		$scope.comment = '',
		$scope.reason = 'Bug', //By default
		$scope.developer,
		$scope.moderator;

		$scope.init = function(userID) {
			$scope.userID = userID;
		}

		$scope.submit = function() {
			if ($scope.comment && $scope.reason) {
				var promise = $http({
					method: "post",
					url: contactPHP,
					data: {
						comment: $scope.comment,
						reason: $scope.reason,
						developer: $scope.developer,
						moderator: $scope.moderator,
					},
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				}).then(function(successResponse) {
					var dialog = ngDialog.open({
						template: 'html/ngDialog/feedback_submitted.html'
					});

					dialog.closePromise.then(function() {
						$window.location.href = '/index.php';
					});
				})
			}
		}
		
	}]);