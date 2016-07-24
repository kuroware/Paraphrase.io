angular./**
* app Module
*
* Description
*/
.module('app', ['ui.bootstrap', 'hc.marked']).
	config(['$provide', 'markedProvider', function($provide, markedProvider) {
		markedProvider.setOptions({gfm: true});
}]).controller('controller', ['$scope', function($scope){

}]);

