var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'ngDialog', 'hljs', 'ngSanitize', 'angucomplete-alt', "chart.js", 'hc.marked']).config(['$provide', 'ChartJsProvider', 'markedProvider', function($provide, ChartJsProvider, markedProvider) {
	$provide.constant('translate', 'postRequests/translate/lookupTranslate.php');
	$provide.value('constructReasons', function(inputReasons) {
		var htmlString = '';
		if (Array.isArray(inputReasons) && inputReasons.length > 0) {
			var htmlString = '<ul>';
			for (var i = 0; i < inputReasons.length; i++) {
				var liString = '<li>' + inputReasons[i] + '</li>';
				var htmlString = htmlString.concat(liString);
			}
		}
		return htmlString;
	});
	$provide.constant('upvoteTranslation', 'postRequests/user/upvoteTranslation.php');
	$provide.constant('downvoteTranslation', 'postRequests/user/downvoteTranslation.php');
	$provide.constant('postTranslation', 'postRequests/translate/postTranslation.php');
	$provide.constant('deleteTranslation', 'postRequests/user/deleteTranslation.php');
	$provide.constant('getRelatedPosts', 'postRequests/translate/relatedPosts.php');
	$provide.constant('postCommentOnTranslation', 'postRequests/user/postCommentOnTranslation.php');
	$provide.constant('getLanguages', 'postRequests/index/fetchLanguages.php');
	$provide.constant('deleteTranslationComment', 'postRequests/user/deleteTranslationComment.php');
	$provide.constant('editTranslation', 'postRequests/user/editTranslation.php');
	$provide.constant('getViewsGraph', 'postRequests/translate/getViewsGraph.php');
	$provide.constant('getTranslateStats', 'postRequests/translate/getTranslateInfo.php');
	$provide.constant('adminDeleteTranslation', 'postRequests/admin/deleteTranslation.php');
	$provide.constant('admins', [2]);

    ChartJsProvider.setOptions('Line', {
    	bezierCurve : false,
    	datasetFill : true
    });
    markedProvider.setOptions({gfm: true});
}]);
app.controller('main', ['$scope', '$http', 'translate', 'ngDialog', 'constructReasons', 'postTranslation', 'upvoteTranslation', 'downvoteTranslation', 'deleteTranslation', 'getRelatedPosts', 'postCommentOnTranslation', '$window', 'getLanguages', 'deleteTranslationComment', 'editTranslation', 'getViewsGraph', 'getTranslateStats', 'adminDeleteTranslation', 'admins', function($scope, $http, translate, ngDialog, constructReasons, postTranslation, upvoteTranslation, downvoteTranslation, deleteTranslation, getRelatedPosts, postCommentOnTranslation, $window, getLanguages, deleteTranslationComment, editTranslation, getViewsGraph, getTranslateStats, adminDeleteTranslation, admins) {
	$scope.translation = null;

	$scope.adminDelete = function(result_id) {
		var promiseDelete = $http({
			method: "post",
			url: adminDeleteTranslation,
			data: {
				result_id: result_id
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});

		promiseDelete.then(function(successResponse) {
			alert('Deleted');
		}, function(errorResponse) {
			alert('Error deleting');
		})
	}

	$scope.editTranslation = function(result_id) {
		for (var i= 0; i <= $scope.data.translations.length; i++) {
			if ($scope.data.translations[i]) {
				if (($scope.data.translations[i].result_id == result_id) && ($scope.data.translations[i].showEdit)) {
					console.log($scope.data.translations[i]);
					//There is an edit to be applied here, apply 
					if ($scope.data.translations[i].edittedLinkedFunction && $scope.data.translations[i].edittedLinkedFunction.originalObject) {
						var function_id = $scope.data.translations[i].edittedLinkedFunction.originalObject.function_id;
					}
					else {
						var function_id = null;
					}
					console.log($scope.data.translations[i].edittedSummary);
					console.log($scope.data.translations[i].showEdit);
					var promiseEdit = $http({
						method: "post",
						url: editTranslation,
						data: {
							edit_type: $scope.data.translations[i].showEdit,
							edit_function_id: function_id,
							edit_summary: $scope.data.translations[i].edittedSummary,
							edit_translation: $scope.data.translations[i].edittedTranslation,
							result_id: result_id
						},
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
					}).then(function(successResponse) {
						console.log('editted');
						var dialog = ngDialog.open({
							template: 'html/ngDialog/success_edit_answer.html'
						});

						dialog.closePromise.then(function() {
							$window.location.reload();
						});

						//More better way to code later
/*						console.log($scope.data.translations[i]);
						$scope.data.translations[i].showEdit = false;
						console.log(successResponse);

						//Try to change the actul display
						console.log(result_id);
						for (var i = 0; i <= $scope.data.translations.length; i++) {
							if ($scope.data.translations[i].result_id == result_id) {
								console.log('found');
								//Update
								if ($scope.data.translations[i].note) {
									$scope.data.translations[i].comment = $scope.data.translations[i].edittedSummary;
								}
								else {
									if ($scope.data.translations[i].single) {
										$scope.data.translations[i].suggested_function.function_name = $scope.data.translations[i].edittedSummary;
									}
									else {
										$scope.data.translations[i].suggested_function = $scope.data.translations[i].edittedLinkedFunction.originalObject;
									}
								}
							}
						}*/
						$scope.data.translations[i].edittedSummary = null;
						$scope.data.translations[i].edittedTranslation = null;
						$scope.data.translations[i].edittedLinkedFunction = null;
					}, function(errorResponse) {
						alert('Error editting');
						console.log(errorResponse);
					});
					break;
				}
			}
		}
	}

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

	$scope.handleEdit = function(result_id) {
		/*
		Prepares the DOM for the editting
		 */
		for (var i= 0; i < $scope.data.translations.length; i++) {
			if ($scope.data.translations[i].result_id == result_id) {
				if ($scope.data.translations[i].note) {
					//Answer is note, edit by note
					$scope.data.translations[i].edittedSummary = $scope.data.translations[i].comment;
					$scope.data.translations[i].showEdit = 1;
					$scope.data.translations[i].edittedTranslation = null;
					break;
				}
				else {
					if ($scope.data.translations[i].single) {
						//This is a single function, edit by single function
						$scope.data.translations[i].edittedLinkedFunction;
						$scope.data.translations[i].edittedTranslation = null;
						$scope.data.translations[i].edittedSummary = null;
						$scope.data.translations[i].showEdit = 2;
						break;
					}
					else {
						$scope.data.translations[i].edittedTranslation = $scope.data.translations[i].suggested_function.function_name;
						$scope.data.translations[i].edittedSummary = $scope.data.translations[i].comment;
						$scope.data.translations[i].showEdit = 3;
						break;
					}
				}
			}
		}
	}
	
	$scope.single = false;
	$scope.postType = 1;

	$scope.showCommentBox = false;

	$scope.data = {}; //Holding object
	$scope.data.x_top_comments = 3;
	$scope.data.showEdit = false;
	$scope.data.single = false; //For the purpose of the form
	$scope.data.linked_function = {};
	$scope.data.postType = 1; //By default

	$scope.editAnswer = function() {

	}

	$scope.deleteComment = function(comment_id) {
		var promiseDeleteTranslationComment = $http({
			method: "post",
			url: deleteTranslationComment,
			data: {
				comment_id: comment_id
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});

		promiseDeleteTranslationComment.then(function(successResponse) {
			//Attempt to remove it
			console.log(successResponse);
			for(var i =0; i < $scope.translations.length; i++) {
				for (var j = 0; j < $scope.translations[i].comments.length; j++) {
					if ($scope.translations[i].comments[j].comment_id == comment_id) {
						//Remove it
						$scope.translations[i].comments.splice(j, 1);
						break;
					}
				}
			}
		}, function(errorResponse) {
			alert('Error deleting comment');
		})
	}

	$scope.incrementTopComments = function() {
		$scope.data.x_top_comments += 3;
	}

	$scope.updateScope = function(str) {
		$scope.alternate = str;
/*		if ($scope.alternate && ($scope.alternate.indexOf(' ') != -1)) {
			console.log('changed');
			$scope.inputType = 'multi';
		}*/
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
					//Searched for a function in the same language, just do a general search
					$window.location.href = '/search.php?search_phrase=' + $scope.alternate + '&src=' + $scope.selectedLanguageID;
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

	var promiseGetRelatedPosts = $http({
		method: "post",
		url: getRelatedPosts,
		data: {
			from_function_id: $scope.from_function_id,
			to_lan_id: $scope.to_lan_id
		},
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
	}).then(function(successResponse) {
		$scope.relatedPosts = successResponse.data;
	}, function(errorResponse) {
		alert('Error fetching related posts');
	});

	$scope.deleteTranslation = function(translation_id) {
		if ($scope.userID != 'None') {
			var promise = $http({
				method: "post",
				url: deleteTranslation,
				data: {
					result_id: translation_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				alert('Successfully deleted');
				//Remove the translation from the array of translations
				for (var i = 0; i < $scope.translations.length; i++) {
					if ($scope.translations[i].result_id == translation_id) {
						console.log('spliced');
						$scope.translations.splice(i, 1);
					}
				}
				console.log(successResponse);

			}, function(errorResponse) {
				alert('Error deleting translation');
				console.log(errorResponse);
			});
		}
	}

	$scope.upvote = function(translation_id) {
		console.log(translation_id);
		if ($scope.userID != 'None') {
			var promise = $http({
				method: "post",
				url: upvoteTranslation,
				data: {
					translation_id: translation_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				//Modify the array accordingly
				for (var i = 0; i < $scope.data.translations.length; i++) {
					if ($scope.data.translations[i].result_id == translation_id) {
						$scope.data.translations[i].upvoted = !$scope.data.translations[i].upvoted;
						if ($scope.data.translations[i].upvoted) {
							$scope.data.translations[i].upvotes++;
							//Set the style
							$scope.data.translations[i].upvoteStyle = {
								'color': 'blue'
							}
							if ($scope.data.translations[i].downvoted) {
								$scope.data.translations[i].downvotes--;
								$scope.data.translations[i].downvoted = false;
								$scope.data.translations[i].downvoteStyle = {
								}
							}	
						}
						else {
							$scope.data.translations[i].upvoteStyle = {};
							$scope.data.translations[i].upvotes--;
						}
					}
				}
			}, function(errorResponse) {
				alert('Error upvoting translation');
			});
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_upvote_answer.html'
			});
		}
	}


	$scope.downvote = function(translation_id) {
		if ($scope.userID != 'None') {
			var promise = $http({
				method: "post",
				url: downvoteTranslation,
				data: {
					translation_id: translation_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				//Modify the array accordingly
				for (var i = 0; i < $scope.data.translations.length; i++) {
					if ($scope.data.translations[i].result_id == translation_id) {
						$scope.data.translations[i].downvoted = !$scope.data.translations[i].downvoted;
						if ($scope.data.translations[i].downvoted) {
							$scope.data.translations[i].downvotes++;
							$scope.data.translations[i].downvoteStyle = {
								'color': 'blue'
							}
							if ($scope.data.translations[i].upvoted) {
								$scope.data.translations[i].upvotes--;
								$scope.data.translations[i].upvoted = false;
								$scope.data.translations[i].upvoteStyle = {};
							}	
						}
						else {
							$scope.data.translations[i].downvotes--;
							$scope.data.translations[i].downvoteStyle = {};
						}
					}
				}
			}, function(errorResponse) {
				alert('Error upvoting translation');
			});
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_upvote_answer.html'
			});
		}
	}

	$scope.showHiddenAnswer = function(result_id) {
		for (var i= 0; i < $scope.data.translations.length; i++) {
			if ($scope.data.translations[i].result_id == result_id) {
				$scope.data.translations[i].default_show = true;
			}
		}
	}

	$scope.init = function(translation_id, function_id, category_id, language1, language2, user_id) {
		var adminsArray = admins;
		var pos = adminsArray.indexOf(user_id);
		if (pos != -1) {
			$scope.data.admin = true;
		}
		else {
			$scope.data.admin = false;
		}

		console.log('caled');
		if (language1 != language2) {
			$scope.translation_id = translation_id;
			$scope.function_id = function_id
			$scope.category_id = category_id;
			$scope.language1 = language1;
			$scope.language2 = language2;
			$scope.userID = user_id;
			$scope.selectedLanguageID = language2;
			console.log($scope.language1);
			console.log($scope.language2);
			console.log($scope.function_id);
			console.log($scope.category_id);
			var promise = $http({
				method: "post",
				url: translate,
				data: {
					category_id: $scope.category_id,
					from_function_id: $scope.function_id,
					lan1: $scope.language1,
					lan2: $scope.language2
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$scope.translations = successResponse.data;
				for (var i =0; i < $scope.translations.length; i++) {
					if (!$scope.translations[i].result_id) {
						//Remove
						$scope.translations.slice(i, 1);
						continue;
					}
					//Load the correct styles and check to see if its default action should be hidden due to high downvotes
					$scope.translations[i].upvoteStyle = {},
						$scope.translations[i].downvoteStyle = {};

					if ($scope.translations[i].upvoted) {
						$scope.translations[i].upvoteStyle = {
							'color': 'blue'
						}
					}
					else if ($scope.translations[i].downvoted) {
						$scope.translations[i].downvoteStyle = {
							'color': 'blue'
						}
					}

					if (($scope.translations[i].upvotes - $scope.translations[i].downvotes) > -3) {
						$scope.translations[i].default_show = true;
					}
					else {
						$scope.translations[i].default_show = false;
					}
				}

				//By default sort to the translations by votes
				$scope.translations.sort(function(a, b) {
					return (parseInt(b.upvotes) - parseInt(b.downvotes) - parseInt(a.upvotes) + parseInt(a.downvotes));
				});
				$scope.data.translations = $scope.translations;
			}, function(errorResponse) {
				console.log(errorResponse);
				alert('Error translating');
			});
			var promiseGetViewsGraph = $http({
				method: "post",
				url: getViewsGraph,
				data: {
					translation_id: $scope.translation_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$scope.data.labels =successResponse.data.labels;
				$scope.data.dataset = successResponse.data.dataset;
				$scope.data.series = successResponse.data.series;
			}, function(errorResponse) {
				alert('Error fetching stats');
			});

			var promiseGetTranslationStats = $http({
				method: "post",
				url: getTranslateStats,
				data: {
					translation_id: $scope.translation_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$scope.upvotes = successResponse.data.upvotes;
				$scope.views = successResponse.data.views;
				$scope.answers = successResponse.data.answers;
				$scope.upvoted = successResponse.data.upvoted;
				$scope.contributors = successResponse.data.contributors;
				$scope.firstAsked = successResponse.data.first_asked;
			}, function(errorResponse){
				alert('Error fetching stats');
			})
		}
		else {
			$window.location.href = '/404.php';
		}
	}

	$scope.databaseAnswer = function() {
		ngDialog.open({
			template: 'html/ngDialog/database_answer.html'
		});
	}

	$scope.changeTab = function(newTab) {
		if ($scope.data.translations) {
			if (newTab == 'Votes') {
				//Sort by votes
				$scope.data.translations.sort(function(a, b) {
					return (parseInt(b.upvotes) - parseInt(b.downvotes) - parseInt(a.upvotes) + parseInt(a.downvotes));
				});
				console.log($scope.data.translations);
			}
			if (newTab == 'Newest') {
				$scope.data.translations.sort(function(a, b) {
					return new Date(b.date_posted) - new Date(a.date_posted);
				})
			}
		}
	}


	$scope.expandReasons = function() {
		if ($scope.translations[0].database_answer) {
			var htmlString = constructReasons($scope.translations[0].reasons);
			console.log(htmlString);
			ngDialog.open({
				plain: true,
				template: '<html><p><center><h2>Reasons</h2><p><h6>The database creates an answer automatically for functions that can be directly translated without needing additional functions. The accuracy may be volatile (as the database searches against the cateogry of the function placement). The top rated answer is automatically shown (does not have to be a database answer) and if you have a better answer, you may submit it below</h6></p>' + htmlString
			});
		}
	}

	$scope.postTranslationComment = function(result_id) {
		if ($scope.userID != 'None') {
			if ($scope.data.commentBox && $scope.data.commentBox.length > 2) {
				console.log($scope.data.commentBox);
				console.log(result_id);
				var promisePostCommentOnTranslation = $http({
					method: "post",
					url: postCommentOnTranslation,
					data: {
						result_id: result_id,
						comment_text: $scope.data.commentBox
					},
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				}).then(function(successResponse) {
					console.log(successResponse);
					$scope.data.commentBox = '';
					$scope.showCommentBox = false;
					//Now populate the comment into the comment box
					var newComment = successResponse.data;
					for (var i = 0; i < $scope.data.translations.length; i++) {
						if ($scope.data.translations[i].result_id == result_id) {
							$scope.data.translations[i].comments.push(newComment);
						}
					}
				}, function(errorResponse) {
					console.log(errorResponse);
					alert('Error posting comment');
				});
			}
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_post_comment.html'
			});
		}
	}

	$scope.submitNoteTranslation = function() {
		console.log($scope.data.single);
		console.log($scope.data.translation);
		console.log($scope.data.comment);
		console.log($scope.data.translation_id);
		console.log($scope.data.postType);
		if ($scope.data.linked_function && $scope.data.linked_function.originalObject) {
			var function_id = $scope.data.linked_function.originalObject.function_id;
		}
		else {
			var function_id = null;
		}
		var promise = $http({
			method: "post",
			url: postTranslation,
			data: {
				comment: $scope.data.comment,
				single_function: $scope.data.single,
				translation_id: $scope.translation_id,
				translation: $scope.data.translation,
				linked_function_id: function_id,
				type: $scope.data.postType
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			console.log(successResponse);
			$scope.submitted = true;
			var dialog = ngDialog.open({
				template: 'html/ngDialog/success_post.html'
			});

			dialog.closePromise.then(function(successResponse) {
				//Refresh the page once promise is resolved
				$window.location.reload();
			})

		}, function(errorResponse) {
			console.log(errorResponse);
			alert('Error submitting translation');
		})
	}

	$scope.singleExpand = function() {
		ngDialog.open({
			template: 'html/ngDialog/single_function.html'
		});
	}
}]);

/*app.controller('translationBlock', ['$scope', '$http', 'postCommentOnTranslation', function($scope, $http, postCommentOnTranslation) {
	$scope.data = {};
}]);*/



app.controller('comments', ['$scope', '$http', function($scope, $http){
	
}])