var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap']).config(function($provide) {
	$provide.constant('fetchPosts', 'postRequests/posts/fetchNewPosts.php');
});

app.controller('main', ['$scope', '$http', 'fetchPosts', function($scope, $http, fetchPosts){
	var promise = $http({
		method: "post",
		url: fetchPosts,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.posts = successResponse.data;
	}, function(errorResponse) {
		alert('Error fetching posts');
		$scope.posts = [];
	});
}]);