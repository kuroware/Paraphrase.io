var app = angular./**
* app Module
*
* Description
*/
module('app', ['textAngular', 'ui.bootstrap']).config(function($provide) {
	$provide.constant(); //Post article constant here
});

app.controller('main', ['$scope', '$http', function($scope, $http){
	$scope.htmlVariable;

	$scope.init = function(userObject) {
		$scope.user = JSON.parse(userObject);
	}
	
}]);