var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'ngDialog', 'angucomplete']).config(function($provide) {
	$provide.constant('getTranslations', 'postRequests/translations/getTranslations.php');
	$provide.constant('getLanguages', 'postRequests/index/fetchLanguages.php');
	$provide.constant('getNewFunctions', 'postRequests/translations/getNewFunctions.php');
	$provide.constant('getTopUsers', 'postRequests/translations/getTopUsers.php');
	$provide.constant('getThisWeek', 'postRequests/translations/getThisWeek.php');
	$provide.constant('getThisMonth', 'postRequests/translations/getThisMonth.php');
	$provide.constant('getStats', 'postRequests/translations/getStats.php');
	$provide.constant('getFeatured', 'postRequests/translations/getFeatured.php');
	$provide.constant('getFewestAnswers', 'postRequests/translations/getFewestAnswers.php');
	$provide.constant('getLanguageInfo', 'postRequests/info/getLanguage.php');
	$provide.constant('upvoteTranslationRequest', 'postRequests/user/upvoteTranslationRequest.php');
	$provide.constant('getTranslationStats', 'postRequests/info/getStatsSearch.php');
	$provide.constant('getLanguagesStats', 'postRequests/info/getLanguagesInfo.php');
});

app.filter('searchFunction', function() {
	return function(translationsArray, searchString) {
		console.log(translationsArray);
		var results = [];
		if (searchString) {
			var searchLength = searchString.length;
			var searchString_trimmed = searchString.replace('.', ' ');
			if (searchString_trimmed == searchString) {
				//No neeed to check for both
				for (var i = 0; i < translationsArray.length; i++) {
					if (translationsArray[i].from_function.function_name.substr(0, searchLength) == searchString) {
						results.push(translationsArray[i]);
					}
				}
			}
			else {
				//Check for both
				for (var i = 0; i < translationsArray.length; i++) {
					if ((translationsArray[i].from_function.function_name.substr(0, searchLength) == searchString) || (translationsArray[i].from_function.function_name.substr(0, searchLength) == searchString_trimmed)) {
						results.push(translationsArray[i]);
					}
				}
			}
			console.log(results);
			return results;
		}
		else {
			return translationsArray
		}

	}
})

app.controller('main', ['$scope', '$http', 'ngDialog', 'getTranslations', 'getLanguages', 'getNewFunctions', 'getTopUsers', 'getThisWeek', 'getThisMonth', 'getStats', 'getFeatured', 'getFewestAnswers', 'getLanguageInfo', 'upvoteTranslationRequest', 'filterFilter', 'searchFunctionFilter', '$window', 'getTranslationStats', 'getLanguagesStats', function($scope, $http, ngDialog, getTranslations, getLanguages, getNewFunctions, getTopUsers, getThisWeek, getThisMonth, getStats, getFeatured, getFewestAnswers, getLanguageInfo, upvoteTranslationRequest, filterFilter, searchFunctionFilter, $window, getTranslationStats, getLanguagesStats){
	$scope.viewMessage = 'To'
	$scope.showing = 'to';
	$scope.selected = 11;
	$scope.default = 1;

	$scope.attemptTranslation = function() {
		$scope.lan1 = $scope.searchCode.originalObject.function_language.language_id;
		$scope.code1 = $scope.searchCode.originalObject.function_name;
		$scope.lan2 = $scope.selectedLanguageID;

		console.log($scope.lan1);
		console.log($scope.lan2);
		console.log($scope.code1);

		if ($scope.lan2 && $scope.lan1 && $scope.code1) {
			$window.location.href = 'translate.php?src=' + $scope.code1 + '&lan1=' + $scope.lan1 + '&lan2='+ $scope.lan2;
		}
	}

	var promiseGetLanguagesStats = $http({
		method: "post",
		url: getLanguagesStats,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.infoFrom = successResponse.data.translations_from;
		$scope.infoTo = successResponse.data.translations_to;
	}, function(errorResponse) {
		alert('Error fetching stats');
	});

	var promiseGetTranslationStats = $http({
		method: "post",
		url: getTranslationStats,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.translationsToday = successResponse.data.translations_done_today;
		$scope.newRequestsToday = successResponse.data.new_translation_requests_today;
	}, function(errorResponse) {
		alert('Error fetching stats');
	});

	$scope.selectedLanguageID = 1; //The selected langauge ID for translation
/*
	$scope.$watch('selectedLanguageID', function(newValue, oldValue) {
		if (newValue && $scope.searchCode.originalObject.function_language.language_id) {
			if (newValue == $scope.searchCode.originalObject) {
				//Attempting to translate function to itself
				$scope.
			}
		}
		else {
			//Some sort of action for undefined language ID
			$scope.ready = false;
		}
	});*/

	$scope.$watch('searchCode', function(newValue, oldValue) {
		console.log(newValue);
		var curLanguageID = newValue.originalObject.function_language.language_id;

		//Splice it out of the available options
		for (var i = 0; i < $scope.selectableLanguages.length; i++) {
			if ($scope.selectableLanguages[i].language_id == curLanguageID) {
				$scope.selectableLanguages.splice(i, 1);
			}
		}

		if (newValue.originalObject.function_language.language_id) {
			//The value is truthy
			if (newValue.originalObject.function_language.language_id == $scope.selectedLanguageID) {
				$scope.selectedLanguageID = null;
			}
		}
		//if (newValue.originalObject.function_language)
	});

	$scope.data = {};

	$scope.$watch('data.searchPhrase', function(newValue, oldValue) {
		console.log();
		$scope.fewestAnswersFiltered = searchFunctionFilter($scope.fewestAnswers, newValue);
		$scope.translationsMonthFiltered = searchFunctionFilter($scope.translationsMonth, newValue);
		$scope.translationsWeekFiltered = searchFunctionFilter($scope.translationsWeek, newValue);
		$scope.translationsFiltered = searchFunctionFilter($scope.translations, newValue);
		$scope.featuredTranslationsFiltered = searchFunctionFilter($scope.featuredTranslations, newValue);
	});

	$scope.to = true;

	function initialLang() {
		this.language_id = 11;
		this.summary = "A programming language's surface form is known as its syntax. Most programming languages are purely textual; they use sequences of text including words, numbers, and punctuation, much like written natural languages. On the other hand, there are some programming languages which are more graphical in nature, using visual relationships between symbols to specify a program. The syntax of a language describes the possible combinations of symbols that form a syntactically correct program. The meaning given to a combination of symbols is handled by semantics (either formal or hard-coded in a reference implementation). Since most languages are textual, this article discusses textual syntax.";
	}

	$scope.curLanguage = new initialLang();

	$scope.init = function(userID) {
		$scope.userID = userID
	}

	$scope.upvoteRequest = function(translationID) {
		if ($scope.userID != 'None') {
			console.log(translationID);
			var promiseUpvote = $http({
				method: "post",
				url: upvoteTranslationRequest,
				data: {
					translation_id: translationID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
			}, function(errorResponse) {
				alert('Error upvoting');
			})
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_upvote.html'
			});
		}
	}

	var promiseGetFewestAnswers = $http({
		method: "post",
		url: getFewestAnswers,
		data: {
			filter: 11,
			to: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.fewestAnswers = successResponse.data;
		$scope.fewestAnswersFiltered = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		console.log(errorResponse);
		alert('Error fetching fewest translations');
	});

	var promiseGetFeatured = $http({
		method: "post",
		url: getFeatured,
		data: {
			filter: 11,
			to: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.featuredTranslations = successResponse.data;
		$scope.featuredTranslationsFiltered = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		console.log(errorResponse);
		alert('Error fetching featured translations');
	});

	var promiseGetStats = $http({
		method: "post",
		url: getStats,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.stats = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		console.log(errorResponse);
		alert('Error fetching stats');
	});

	var promiseGetTopUsers = $http({
		method: "post",
		url: getTopUsers,
		data: {
			filter: 11
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.topUsers = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		console.log(errorResponse);
		alert('Error fetching top users');
	});

	var promiseGetMonth = $http({
		method: "post",
		url: getThisMonth,
		data: {
			filter: 11,
			to: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.translationsMonth = successResponse.data;
		$scope.translationsMonthFiltered = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		alert('Error fetching translations');
	});

	var promiseGetWeek = $http({
		method: "post",
		url: getThisWeek,
		data: {
			filter: 11,
			to: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.translationsWeek = successResponse.data;
		$scope.translationsWeekFiltered =successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		alert('Error fetching weekly translations');
	});

	var promiseGetTranslations = $http({
		method: "post",
		url: getTranslations,
		data: {
			filter: 11,
			to: true
		},	
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.translations = successResponse.data;
		$scope.translationsFiltered = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		alert('Error fetching translations');
	});

	var promiseGetNewFunctions = $http({
		method: "post",
		url: getNewFunctions,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.newFunctions = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		alert('Error fetching new functions');
	});

	var promiseGetLanguages = $http({
		method: "post",
		url: getLanguages,
		data: {
			dev: true
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.languages = successResponse.data;
		$scope.selectableLanguages = successResponse.data;

		var example = function() {
			this.language_id = 11;
			this.language_name = 'All';
		}
		var prepend = new example();
		$scope.languages.unshift(prepend);
		for (var i = 0; i < $scope.languages.length; i++) {
			$scope.languages[i].classStyle = 'list-group-item';
		}
		$scope.languages[0].classStyle += ' active';
		console.log($scope.languages);
	}, function(errorResponse) {
		alert('Error fetching translations');
	});

	$scope.$watch('to', function(newValue, oldValue) {
		if ((typeof newValue) == "boolean") {
			if ($scope.selected != 11) {
				//Makes no sense to filter on all
				$scope.newSelect($scope.selected);
			}
		}
		$scope.viewMessage = ($scope.to) ? 'To': 'From';
	})

	$scope.newSelect = function(languageID) {
		$scope.selected = languageID;
		if ($scope.selected != 11) {
			var promiseGetCurLanguage = $http({
				method: "post",
				url: getLanguageInfo,
				data: {
					language_id: $scope.selected
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.curLanguage = successResponse.data;
			}, function(errorResponse) {
				alert('Error upvoting');
			});
		}
		else {
			$scope.curLanguage = new initialLang;
		}
		for (var i = 0; i < $scope.languages.length; i++) {
			$scope.languages[i].classStyle = 'list-group-item';
			if ($scope.languages[i].language_id == languageID) {
				$scope.languages[i].classStyle += ' active';
			}
		}
		var promiseGetMonth = $http({
			method: "post",
			url: getThisMonth,
			data: {
				filter: languageID,
				to: $scope.to
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.translationsMonth = successResponse.data;
			console.log($scope.data.searchString + 'is the search sting');
			$scope.translationsMonthFiltered = searchFunctionFilter($scope.translationsMonth, $scope.data.searchPhrase);
			console.log(successResponse.data);
		}, function(errorResponse) {
			alert('Error fetching translations');
		});

		var promiseGetWeek = $http({
			method: "post",
			url: getThisWeek,
			data: {
				filter: languageID,
				to: $scope.to
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.translationsWeek = successResponse.data;
			$scope.translationsWeekFiltered = searchFunctionFilter($scope.translationsWeek, $scope.data.searchPhrase);
			console.log(successResponse.data);
		}, function(errorResponse) {
			alert('Error fetching weekly translations');
		});

		var promiseGetTranslations = $http({
			method: "post",
			url: getTranslations,
			data: {
				filter: languageID,
				to: $scope.to
			},	
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.translations = successResponse.data;
			$scope.translationsFiltered = searchFunctionFilter($scope.translations, $scope.data.searchPhrase);
			console.log($scope.translations);
			console.log(successResponse.data);
		}, function(errorResponse) {
			alert('Error fetching translations');
		});
		var promiseGetFewestAnswers = $http({
			method: "post",
			url: getFewestAnswers,
			data: {
				filter: languageID,
				to: $scope.to
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.fewestAnswers = successResponse.data;
			$scope.fewestAnswersFiltered = searchFunctionFilter($scope.fewestAnswers, $scope.data.searchPhrase);
			console.log(successResponse.data);
		}, function(errorResponse) {
			console.log(errorResponse);
			alert('Error fetching fewest translations');
		});
		console.log(languageID);
		var promiseGetFeatured = $http({
			method: "post",
			url: getFeatured,
			data: {
				filter: languageID,
				to: $scope.to
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.featuredTranslations = successResponse.data;
			$scope.featuredTranslationsFiltered = searchFunctionFilter($scope.featuredTranslations, $scope.data.searchPhrase);
			console.log(successResponse.data);
		}, function(errorResponse) {
			console.log(errorResponse);
			alert('Error fetching featured translations');
		});		
	}
}]);

app.controller('translationRequest', ['$scope', function($scope){

	$scope.increment = function() {
		if ($scope.userID != 'None') {
			$scope.translation.upvotes = parseInt($scope.translation.upvotes);
			$scope.translation.upvoted = !$scope.translation.upvoted;
			if ($scope.translation.upvoted == true) {
				$scope.translation.upvotes += 1;
			}
			else {
				$scope.translation.upvotes -= 1;
			}
		}
	}

}]);