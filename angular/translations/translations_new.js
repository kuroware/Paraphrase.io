var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'ngDialog', 'angucomplete-alt', 'infinite-scroll', 'hc.marked']).config(['$provide', 'markedProvider', function($provide, markedProvider) {
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
	$provide.constant('getRecentAnnotatedFunctions', 'postRequests/function/recentAnnotatedFunctionsInLanguage.php');
	$provide.constant('scrollIncrement', 5);
	$provide.constant('getLanguageCount', 'postRequests/language/getLanguageQuestionsCount.php');
	$provide.constant('getLanguagePopularity', 'postRequests/language/getLanguagePopularity.php');

	markedProvider.setOptions({gfm: true});
}]);

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
});

app.controller('main', ['$scope', '$http', 'ngDialog', 'getTranslations', 'getLanguages', 'getNewFunctions', 'getTopUsers', 'getThisWeek', 'getThisMonth', 'getStats', 'getFeatured', 'getFewestAnswers', 'getLanguageInfo', 'upvoteTranslationRequest', 'filterFilter', 'searchFunctionFilter', '$window', 'getTranslationStats', 'getLanguagesStats', 'getRecentAnnotatedFunctions', 'scrollIncrement', 'getLanguageCount', '$q', 'getLanguagePopularity', function($scope, $http, ngDialog, getTranslations, getLanguages, getNewFunctions, getTopUsers, getThisWeek, getThisMonth, getStats, getFeatured, getFewestAnswers, getLanguageInfo, upvoteTranslationRequest, filterFilter, searchFunctionFilter, $window, getTranslationStats, getLanguagesStats, getRecentAnnotatedFunctions, scrollIncrement, getLanguageCount, $q, getLanguagePopularity){

	$scope.data = {};

	$scope.data.lastTabStyle = {
		'margin-top':  '10px',
		'padding-left' : '15px',
		'border-left': '1px solid #ddd',
		'height': '30px'
	}

	$scope.unstyleTab = function() {
		$scope.data.lastTabStyle = {
			'margin-top':  '10px',
			'padding-left' : '15px'
		}	
	}

	$scope.styleTab = function() {
		$scope.data.lastTabStyle = {
			'margin-top':  '10px',
			'padding-left' : '15px',
			'border-left': '1px solid #ddd',
			'height': '30px'
		}
	}

	$scope.viewMessage = 'To'
	$scope.showing = 'to';
	$scope.selected = 11;
	$scope.default = 1;

	$scope.init = function(userID, language) {
		//Initialize the user interface
		var deferred = $q.defer();

		$scope.userID = userID,
		$scope.data = (!$scope.data) ? {} : $scope.data,
		$scope.data.view = {
			'name': 'To',
			'to': true,
			'from': false,
			'falseName': 'From'
		},

		$scope.data.possibleLanguages = [
			{
				'language_id': 11,
				'language_name': 'all',
				'summary': 'A programming language is a formal constructed language designed to communicate instructions to a machine, particularly a computer. Programming languages can be used to create programs to control the behavior of a machine or to express algorithms.'
			},
			{
				'language_id': 1,
				'language_name': 'php',
				'summary': "PHP is a server-side scripting language designed for web development but also used as a general-purpose programming language. As of January 2013, PHP was installed on more than 240 million websites (39% of those sampled) and 2.1 million web servers."
			},
			{
				'language_id': 2,
				'language_name': 'javascript',
				'summary': 'JavaScript is a high level, dynamic, untyped, and interpreted programming language.[6] It has been standardized in the ECMAScript language specification.[7] Alongside HTML and CSS, it is one of the three essential technologies of World Wide Web content production; the majority of websites employ it and it is supported by all modern web browsers without plug-ins.[6] JavaScript is prototype-based with first-class functions, making it a multi-paradigm language, supporting object-oriented,[8] imperative, and functional programming styles'
			},
			{
				'language_id': 4,
				'language_name': 'python',
				'summary': 'Python is a widely used general-purpose, high-level programming language. Its design philosophy emphasizes code readability, and its syntax allows programmers to express concepts in fewer lines of code than would be possible in languages such as C++ or Java. The language provides constructs intended to enable clear programs on both a small and large scale.'
			},
			{
				'language_id': 5,
				'language_name': 'ruby',
				'summary': 'Ruby is a dynamic, reflective, object-oriented, general-purpose programming language. It was designed and developed in the mid-1990s by Yukihiro "Matz" Matsumoto in Japan. According to its authors, Ruby was influenced by Perl, Smalltalk, Eiffel, Ada, and Lisp. It supports multiple programming paradigms, including functional, object-oriented, and imperative. It also has a dynamic type system and automatic memory management.'
			}
		];

		for (var i = 0; i < $scope.data.possibleLanguages.length; i++) {
			if ($scope.data.possibleLanguages[i].language_name == language) {
				$scope.data.language = $scope.data.possibleLanguages[i];
			}
		}
		if (!$scope.data.language) {
			$scope.data.language = $scope.data.possibleLanguages[0];
		}

		$scope.fewestAnswers = [],
		$scope.fewestAnswersFiltered = [],
		$scope.featuredTranslations = [],
		$scope.featuredTranslationsFiltered = [],
		$scope.newestTranslations = [],
		$scope.newestTranslationsFiltered = [],
		$scope.translationsWeek = [],
		$scope.translationsWeekFiltered = [],
		$scope.translationsMonth = [],
		$scope.translationsMonthFiltered = [];
		
		//Function to get the translations for the language id	
		$scope.getTranslations = function() {
			var promiseGetFewestAnswers = $http({
				method: "post",
				url: getFewestAnswers,
				data: {
					filter: $scope.data.language.language_id,
					to: $scope.data.view.to
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.fewestAnswers = successResponse.data;
				$scope.fewestAnswersFiltered = successResponse.data;
				console.log(successResponse.data);
			}, function(errorResponse) {
				console.log(errorResponse);
				//alert('Error fetching fewest translations');
			});

			var promiseGetFeatured = $http({
				method: "post",
				url: getFeatured,
				data: {
					filter: $scope.data.language.language_id,
					to: $scope.data.view.to
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.featuredTranslations = successResponse.data;
				$scope.featuredTranslationsFiltered = $scope.featuredTranslations
				console.log(successResponse.data);
			}, function(errorResponse) {
				console.log(errorResponse);
				//alert('Error fetching featured translations');
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
				//alert('Error fetching stats');
			});

			var promiseGetMonth = $http({
				method: "post",
				url: getThisMonth,
				data: {
					filter: $scope.data.language.language_id,
					to: $scope.data.view.to
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.translationsMonth = successResponse.data;
				$scope.translationsMonthFiltered = successResponse.data;
				console.log(successResponse.data);
			}, function(errorResponse) {
				//alert('Error fetching translations');
			});

			var promiseGetWeek = $http({
				method: "post",
				url: getThisWeek,
				data: {
					filter: $scope.data.language.language_id,
					to: $scope.data.view.to
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.translationsWeek = successResponse.data;
				$scope.translationsWeekFiltered =successResponse.data;
				console.log(successResponse.data);
			}, function(errorResponse) {
				//alert('Error fetching weekly translations');
			});

			var promiseGetTranslations = $http({
				method: "post",
				url: getTranslations,
				data: {
					filter: $scope.data.language.language_id,
					to: $scope.data.view.to
				},	
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.translations = successResponse.data;
				$scope.translationsFiltered = successResponse.data;
				console.log(successResponse.data);
			}, function(errorResponse) {
				//alert('Error fetching translations');
			});
		}

		//Function that loads the side bar for the language specific
		$scope.getSidebar = function() {
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
				//alert('Error fetching top users');
			});

			var promiseGetLanguageStats = $http({
				method: "post",
				url: getLanguageCount,
				data: {
					language_id: $scope.data.language.language_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$scope.data.to = successResponse.data.translations_to;
				$scope.data.from = successResponse.data.translations_from;
			}, function(errorResponse) {
				console.log(errorResponse);
			});

			var promiseGetRecentAnnotatedFunctions = $http({
				method: "post",
				url: getRecentAnnotatedFunctions,
				data: {
					language_id: $scope.data.language.language_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				$scope.data.recentAnnotatedFunctions = successResponse.data;
				console.log(successResponse);
			}, function(errorResponse) {
				console.log('Error loading recent annotated functions');
			});

			if ($scope.data.language.language_id != 11) {
				var promiseGetLanguagePopularity = $http({
					method: "post",
					url: getLanguagePopularity,
					data: {
						language_id: $scope.data.language.language_id
					},
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				}).then(function(successResponse) {
					$scope.data.language.weeklyRank = parseInt(successResponse.data.weekly_rank);
					$scope.data.language.lifetimeRank = parseInt(successResponse.data.lifetime_rank);

					//Generate the messsage
					$scope.data.language.rankingMessage = '';
					$scope.data.language.rankingMessage += $scope.data.language.weeklyRank
					switch ($scope.data.language.weeklyRank) {
						case 1:
							$scope.data.language.rankingMessage += 'st';
							break;
						case 2:
							$scope.data.language.rankingMessage += 'nd';
							break;
						case 3:
							$scope.data.language.rankingMessage += 'rd';
							break;
						default:
							$scope.data.language.rankingMessage += 'th';
							break;
					}
					$scope.data.language.rankingMessage +=' most popular this week, ';
					$scope.data.language.rankingMessage += $scope.data.language.lifetimeRank;
					switch ($scope.data.language.lifetimeRank) {
						case 1:
							$scope.data.language.rankingMessage += 'st';
							break;
						case 2:
							$scope.data.language.rankingMessage += 'nd';
							break;
						case 3:
							$scope.data.language.rankingMessage += 'rd';
							break;
						default:
							$scope.data.language.rankingMessage += 'th';
							break;
					}
					$scope.data.language.rankingMessage += ' most popular on site lifetime';
				});
			}
		}

		//Now call the functions, first get the translations
		$scope.getTranslations();

		//Now call the sidebar
		$scope.getSidebar();
	}

	$scope.changeView = function() {
		/*
		Inverts the current view
		 */
		$scope.data.view.to = !$scope.data.view.to,
		$scope.data.view.from = !$scope.data.view.from;
		$scope.data.view.name = ($scope.data.view.to) ? 'To': 'From';
		$scope.data.view.falseName = ($scope.data.view.to) ? 'From' : 'To';

		$scope.getTranslations(); //Get the new translations with the new view
	}

	$scope.scrollingFunction = function() {
		if ($scope.featuredTranslations) {
			/*console.log($scope.featuredTranslationsFiltered.length);*/
			var displayLength = ($scope.featuredTranslationsFiltered) ? 0 : $scope.featuredTranslationsFiltered.length;
			if (($scope.featuredTranslationsFiltered.length + scrollIncrement) < $scope.featuredTranslations.length) {
				for (var i = displayLength; i < (displayLength + scrollIncrement); i++) {
					$scope.featuredTranslationsFiltered.push($scope.featuredTranslations[i]);
				}
			}
			else {
				$scope.featuredTranslationsFiltered = $scope.featuredTranslations;
			}
		}
	}

	$scope.inputType = 'function';

	$scope.updateScope = function(str) {
		$scope.alternate = str;
	}

	$scope.askQuestion = function() {
		$window.location.href = 'askQuestion.php';
	}

	$scope.attemptTranslation = function() {
		console.log($scope.searchPhrase);
		console.log($scope.alternate);
		/*
			Three main possibilites when searching which can be split into desired translations or not
			Desired Translation:
			- Searcing for a possible translation, but without a linked function id
			- Searching for a direct translation with a linked function id
			Un-desired translation
			- Searching for a function in general
			- Searching for a concept
		*/
		if ($scope.selectedLanguageID <= 10) {
			//There is a selected destination language, figure out if it is a desired translation
			if ($scope.searchPhrase && $scope.searchPhrase.originalObject && ($scope.selectedLanguageID <= 10)) {
				console.log($scope.originalObject);
				if ($scope.searchPhrase.originalObject.function_language.language_id != $scope.selectedLanguageID) {
					var functionID = $scope.searchPhrase.originalObject.function_id
					var functionName = $scope.searchPhrase.originalObject.function_name;
					$window.location.href = '/translate.php?ffi=' + functionID + '&lan=' + $scope.selectedLanguageID;
				}
				else {
					//Searched for a function in the same language, just redirect to the function page
					var functionID = $scope.searchPhrase.originalObject.function_id;
					$window.location.href = '/function.php?id=' + functionID;
/*					var functionName = $scope.searchPhrase.originalObject.function_name;
					$window.location.href = '/search.php?search_phrase=' + functionName + '&src=' + $scope.selectedLanguageID;*/
				}
			}
			else {
				//No linked function id, do a generla search
				$window.location.href = '/search.php?search_phrase=' + $scope.alternate + '&src=' + $scope.selectedLanguageID;
			}
		}
		else {
			$window.location.href = '/search.php?search_phrase=' + $scope.alternate + '&src=' + $scope.selectedLanguageID;
		}
	}



	$scope.selectedLanguageID = 1; 

/*	$scope.$watch('searchCode', function(newValue, oldValue) {
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
	});

	$scope.$watch('data.searchPhrase', function(newValue, oldValue) {
		console.log();
		$scope.fewestAnswersFiltered = searchFunctionFilter($scope.fewestAnswers, newValue);
		$scope.translationsMonthFiltered = searchFunctionFilter($scope.translationsMonth, newValue);
		$scope.translationsWeekFiltered = searchFunctionFilter($scope.translationsWeek, newValue);
		$scope.translationsFiltered = searchFunctionFilter($scope.translations, newValue);
		$scope.featuredTranslationsFiltered = searchFunctionFilter($scope.featuredTranslations, newValue);
	});*/

	$scope.to = true;

	function initialLang() {
		this.language_id = 11;
		this.summary = "A programming language's surface form is known as its syntax. Most programming languages are purely textual; they use sequences of text including words, numbers, and punctuation, much like written natural languages. On the other hand, there are some programming languages which are more graphical in nature, using visual relationships between symbols to specify a program. The syntax of a language describes the possible combinations of symbols that form a syntactically correct program. The meaning given to a combination of symbols is handled by semantics (either formal or hard-coded in a reference implementation). Since most languages are textual, this article discusses textual syntax.";
	}

	$scope.curLanguage = new initialLang();

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
				//Modify value
				
				var arrayLoop = [$scope.translationsFiltered, $scope.featuredTranslationsFiltered, $scope.fewestAnswersFiltered, $scope.translationsWeekFiltered, $scope.translationsMonthFiltered];

				for (var i = 0; i < arrayLoop.length; i++) {
					for (var j = 0; j < arrayLoop[i].length; j++) {
						if (arrayLoop[i][j].translation_id == translationID) {
							console.log('found');
							arrayLoop[i][j].upvoted = !arrayLoop[i][j].upvoted;
							if (arrayLoop[i][j].upvoted) {
								arrayLoop[i][j].upvotes++;
							}
							else {
								arrayLoop[i][j].upvotes--;
							}
						}
					}
				}
			}, function(errorResponse) {
				alert('Error upvoting');
			});
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_upvote.html'
			});
		}
	}

	var promiseGetNewFunctions = $http({
		method: "post",
		url: getNewFunctions,
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.newFunctions = successResponse.data;
		console.log(successResponse.data);
	}, function(errorResponse) {
		//alert('Error fetching new functions');
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

/*		var example = function() {
			this.language_id = 11;
			this.language_name = 'All';
		}
		var prepend = new example();
		$scope.languages.unshift(prepend);*/
		for (var i = 0; i < $scope.languages.length; i++) {
			$scope.languages[i].classStyle = 'list-group-item';
		}
		$scope.languages[0].classStyle += ' active';
		console.log($scope.languages);
	}, function(errorResponse) {
		//alert('Error fetching translations');
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

/*	$scope.newSelect = function(languageID) {
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
			$scope.translationsMonthFiltered =s earchFunctionFilter($scope.translationsMonth, $scope.data.searchPhrase);
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
	}*/
}]);

app.controller('translationRequest', ['$scope', function($scope){

}]);