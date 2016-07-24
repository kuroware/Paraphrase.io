var app = angular./**
* app Module
*
* Description
*/
module('app', ['ui.bootstrap', 'hljs', 'ngDialog', 'ngSanitize', 'angucomplete-alt', 'hc.marked']).config(['$provide', 'markedProvider', function($provide, markedProvider) {
	$provide.constant('lookupFunction', 'postRequests/info/lookupFunction.php');
	$provide.constant('editParameters', 'postRequests/user/editParameters.php');
	$provide.constant('getFunctionParameters', 'postRequests/function/getFunctionParameters.php');
	$provide.constant('editFunction', 'postRequests/user/editFunction.php');
	$provide.constant('postFunctionNote', 'postRequests/user/postFunctionNote.php');
	$provide.constant('upvoteFunctionNote', 'postRequests/user/upvoteFunctionNote.php');
	$provide.constant('downvoteFunctionNote', 'postRequests/user/downvoteFunctionNote.php');
	$provide.constant('editFunctionNote', 'postRequests/user/editFunctionNote.php');
	$provide.constant('getFunctionNotes', 'postRequests/function/getFunctionNotes.php');
	$provide.constant('deleteFunctionNote', 'postRequests/user/deleteFunctionNote.php');
	$provide.constant('getSuperCategories', 'postRequests/info/getSuperCategories.php');
	$provide.constant('categorizationProject', 'postRequests/user/categorizeFunction.php');
	$provide.constant('getRelatedTranslations', 'postRequests/function/getRelatedTranslations.php');
	$provide.constant('getLanguages', 'postRequests/index/fetchLanguages.php');
	$provide.constant('getUserReputation', 'postRequests/user/getUserReputation.php');

	markedProvider.setOptions({gfm: true});
}]);

app.controller('main', ['$scope', '$http', 'lookupFunction', 'editParameters', 'ngDialog', 'getFunctionParameters', 'editFunction', 'postFunctionNote', 'upvoteFunctionNote', 'downvoteFunctionNote', 'editFunctionNote', 'getFunctionNotes', 'deleteFunctionNote', 'getSuperCategories', 'categorizationProject', 'getRelatedTranslations', '$window', 'getLanguages', 'getUserReputation', function($scope, $http, lookupFunction, editParameters, ngDialog, getFunctionParameters, editFunction, postFunctionNote, upvoteFunctionNote, downvoteFunctionNote, editFunctionNote, getFunctionNotes, deleteFunctionNote, getSuperCategories, categorizationProject, getRelatedTranslations, $window, getLanguages, getUserReputation) {

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

	$scope.languageFunction;

	$scope.inputType = 'function';

	$scope.data = {}; //Data holding object to prevent primitive scopes
	$scope.data.parameterType = 9;
	$scope.data.returnType = 10;
	$scope.data.deleteType = 11;
	$scope.data.newParameters = [];
	$scope.data.newReturns = [];
	$scope.data.votedSuperID = 6; //By default

	$scope.data.tab = 'Documentation';

	$scope.data.selectableBigO = [
		{
			column: 2,
			label: 'Big O Average (Expected) Case'
		},
		{
			column: 4,
			label:'Big O Worst Case'
		},
		{
			column: 6,
			label: 'Big O Best Case' 
		}
	];

	$scope.data.addedBigO = 2;

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

	$scope.switchTab = function(newTab) {
		$scope.data.tab = newTab;
	}

	$scope.categorizeFunction = function() {
		if ($scope.userID != 'None') {
			if ($scope.data.votedSuperID) {
				console.log($scope.data.votedSuperID);
				console.log($scope.functionID);
				var promiseCategorizeFunction = $http({
					method: "post",
					url: categorizationProject,
					data: {
						function_id: $scope.functionID,
						category_id: $scope.data.votedSuperID
					},
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
				});

				promiseCategorizeFunction.then(function(successResponse) {
					console.log(successResponse);
					ngDialog.open({
						template: 'html/ngDialog/success_vote.html'
					});
				}, function(errorResponse) {
					alert('Error voting');
				})
			}
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_vote.html'
			});
		}
	}

	$scope.addBigO = function() {
		var column2 = $scope.data.addedBigO + 1;
		$scope.editBigO($scope.data.addedBigO, $scope.data.addBigOCase, column2, $scope.data.addBigOEdit);
	}

	$scope.deleteNote = function(note_id) {
		var promiseDeleteFunctionNote = $http({
			method: "post",
			url: deleteFunctionNote,
			data: {
				note_id: note_id
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});

		promiseDeleteFunctionNote.then(function(successResponse) {
			//Splice it out of the array
			for (var i = 0; i < $scope.data.functionNotes.length; i++) {
				if ($scope.data.functionNotes[i].note_id == note_id) {
					$scope.data.functionNotes.splice(i, 1);
					break;
				}
			}
		}, function(errorResponse) {
			alert('Error deleting');
		})
	}

	$scope.postNote = function() {
		if ($scope.data.functionNoteBox && $scope.data.functionNoteBox.length >= 10) {
			var promisePostFunctionNote = $http({
				method: "post",
				url: postFunctionNote,
				data: {
					note_text: $scope.data.functionNoteBox,
					function_id: $scope.functionID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			});

			promisePostFunctionNote.then(function(successResponse) {
				console.log(successResponse);
				$scope.data.functionNoteBox = '';

				//Push the new note into the function notes
				$scope.data.functionNotes.unshift(successResponse.data);
			}, function(errorResponse) {
				alert('Error posting function note');
			})
		}
	}

	$scope.editFunctionNote = function(note_id_to_edit, edited_note_text) {
		if ($scope.userID != 'Note') {
			var promiseEditFunctionNote = $http({
				method: "post",
				url: editFunctionNote,
				data: {
					note_id: note_id_to_edit,
					note_text: edited_note_text
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			});

			promiseEditFunctionNote.then(function(successResponse) {
				console.log(successResponse);
			}, function(errorResponse) {
				alert('Error editting function note')
			});
		}
		else {
			return;
		}
	}

	$scope.upvoteFunctionNote = function(note_id) {
		if ($scope.userID != 'None') {
			var promiseUpvoteNote = $http({
				method: "post",
				url: upvoteFunctionNote,
				data: {
					note_id: note_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				for(var i = 0; i < $scope.data.functionNotes.length; i++) {
					if ($scope.data.functionNotes[i].note_id == note_id) {
						$scope.data.functionNotes[i].upvoted = !$scope.data.functionNotes[i].upvoted;
						if ($scope.data.functionNotes[i].upvoted) {
							if ($scope.data.functionNotes[i].downvoted) {
								$scope.data.functionNotes[i].downvoted = false;
								$scope.data.functionNotes[i].downvotes--;
								$scope.data.functionNotes[i].downvoteStyle = {};
								$scope.data.functionNotes[i].upvotes++;
								$scope.data.functionNotes[i].upvoteStyle = {
									'color': 'blue'
								};
							}
							else {
								$scope.data.functionNotes[i].upvoteStyle = {
									'color': 'blue'
								};
								$scope.data.functionNotes[i].upvotes++;
							}
						}
						else {
							$scoe.data.functionNotes[i].upvoteStyle = {};
							$scope.data.functionNotes[i].upvotes--;
						}
					}
				}
			}, function(errorResponse) {
				alert('Error upvoting');
			});
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_upvote_note.html'
			});
		}
	}

	$scope.downvoteFunctionNote = function(note_id) {
		if ($scope.userID != 'None') {
			var promiseDownvoteNote = $http({
				method: "post",
				url: downvoteFunctionNote,
				data: {
					note_id: note_id
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				for(var i = 0; i < $scope.data.functionNotes.length; i++) {
					if ($scope.data.functionNotes[i].note_id == note_id) {
						$scope.data.functionNotes[i].downvoted = !$scope.data.functionNotes[i].downvoted;
						if ($scope.data.functionNotes[i].downvoted) {
							if ($scope.data.functionNotes[i].upvoted) {
								$scope.data.functionNotes[i].upvoted = false;
								$scope.data.functionNotes[i].upvotes--;
								$scope.data.functionNotes[i].downvotes++;
								$scope.data.functionNotes[i].downvoteStyle = {
									'color': 'blue'
								};
								$scope.data.functionNotes[i].upvoteStyle = {};
							}
							else {
								$scope.data.functionNotes[i].downvoteStyle = {
									'color': 'blue'
								};
								$scope.data.functionNotes[i].downvotes++;
							}
						}
						else {
							$scope.data.functionNotes[i].downvote = {};
							$scope.data.functionNotes[i].downvotes--;
						}
					}
				}
			}, function(errorResponse) {
				alert('Error upvoting');
			});
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_downvote_note.html'
			});
		}
	}

	$scope.editBigO = function(column1, val1, column2, val2) {
		if ($scope.userID != 'None') {
			if (parseInt($scope.points) > 150) {
				if (column1 && val1) {
					console.log(column1);
					console.log(val1);
					var promiseEditFunction = $http({
						method: "post",
						url: editFunction,
						data: {
							column: column1,
							val: val1,
							function_id: $scope.functionID
						},
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
					}).then(function(successResponse) {
						console.log(successResponse);
					}, function(errorResponse) {
						alert('Error editing');
					});

					//Edit the summary pair
					var promiseEditFunction = $http({
						method: "post",
						url: editFunction,
						data: {
							column: column2,
							val: val2,
							function_id: $scope.functionID
						},
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
					}).then(function(successResponse) {
						console.log(successResponse);
					}, function(errorResponse) {
						alert('Error editing');
					});
				}
			}
			else {
				ngDialog.open({
					template: 'html/ngDialog/not_enough_rep.html'
				});
			}
		}
		else {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});	
		}
	}

	$scope.editParameter = function(parameter_id, newName, newDescription, type) {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
		else {
			if (parseInt($scope.points) > 150) {
				if ((type == 7) || (type == 8)) {
					console.log(parameter_id);
					console.log(newDescription);
					var promiseEditParameter = $http({
						method: "post",
						url: editParameters,
						data: {
							linked_id: parameter_id,
							new_edit: newName,
							reason: newDescription,
							type: type
						},
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
					}).then(function(successResponse) {
						console.log(successResponse);
						if (type == 7) {
							for (var i = 0; i < $scope.functionParameters.length; i++) {
								if ($scope.functionParameters[i].parameter_id == parameter_id) {
									$scope.functionParameters[i].parameter_name = newName;
									$scope.functionParameters[i].parameter_description = newDescription;
								}
							}
						}
						else {
							for (var i = 0; i < $scope.functionReturns.length; i++) {
								if ($scope.functionReturns[i].parameter_id == parameter_id) {
									$scope.functionReturns[i].parameter_name = newName;
									$scope.functionReturns[i].parameter_description = newDescription;
								}
							}
						}
					}, function(errorResponse) {
						alert('Error posting');
					})
				}
			}
			else {
				ngDialog.open({
					template: 'not_enough_rep.html'
				});
			}
		}
	}

	$scope.incrementNewParameters = function() {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
		else {
			if (parseInt($scope.points) > 150) {
				var newParameter = {
					parameter_name: null,
					function_id: $scope.functionID,
					parameter_description: null,
					type: 0
				};
				$scope.data.newParameters.push(newParameter);
			}
			else {
				ngDialog.open({
					template: 'not_enough_rep.html'
				});
			}
		}
	}

	$scope.incrementNewReturns = function() {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
		else {
			var newReturn = {
				parameter_name: null,
				function_id: $scope.functionID,
				parameter_description: null,
				type: 1
			};
			$scope.data.newReturns.push(newReturn);
		}
	}

	$scope.deleteReturn = function(parameter_id) {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
		var promise = $http({
			method:"post",
			url: editParameters,
			data: {
				type: $scope.data.deleteType,
				linked_id: parameter_id,
				new_edit: 'Some text'
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});
		promise.then(function(successResponse) {
			for (i = 0; i < $scope.functionReturns.length; i++) {
				if ($scope.functionReturns[i].parameter_id == parameter_id) {
					$scope.functionReturns.splice(i, 1);
					break;
				}
			}
		}, function(errorResponse) {
			alert('Error deleting');
		});
	}

	$scope.deleteParameter = function(parameter_id) {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
		var promise = $http({
			method:"post",
			url: editParameters,
			data: {
				type: $scope.data.deleteType,
				linked_id: parameter_id,
				new_edit: 'Some text'
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});
		promise.then(function(successResponse) {
			for (i = 0; i < $scope.functionParameters.length; i++) {
				if ($scope.functionParameters[i].parameter_id == parameter_id) {
					$scope.functionParameters.splice(i, 1);
					break;
				}
			}
		}, function(errorResponse) {
			alert('Error deleting');
		});
	}

	$scope.submitParameters = function() {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
		else {
			if (parseInt($scope.points) > 150) {
				if ($scope.data.newParameters.length > 0) {
					var loopThrough = $scope.data.newParameters;
					for (var i = 0; i < loopThrough.length; i++) {
						if (loopThrough[i].parameter_name && loopThrough[i].parameter_description) {
							var promise = $http({
								method:"post",
								url: editParameters,
								data: {
									type: $scope.data.parameterType,
									linked_id: $scope.functionID,
									new_edit: loopThrough[i].parameter_name,
									reason: loopThrough[i].parameter_description
								},
								headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
							});
							promise.then(function(successResponse) {
								$scope.data.newParameters.splice(i, 1);
								$scope.functionParameters.push(successResponse.data);
							}, function(errorResponse) {
								alert('Error posting parameter');
							});
						}
					}
				}
			}
			else {
				ngDialog.open({
					template: 'html/ngDialog/not_enough_rep.html'
				});
			}
		}
	}

	$scope.submitReturns = function() {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
		else {
			if ($scope.reputation > 150) {
				if ($scope.data.newReturns.length > 0) {
					var loopThrough = $scope.data.newReturns;
					for (var i = 0; i < loopThrough.length; i++) {
						console.log(loopThrough[i]);
						if (loopThrough[i].parameter_name && loopThrough[i].parameter_description) {
							var promise = $http({
								method:"post",
								url: editParameters,
								data: {
									type: $scope.data.returnType,
									linked_id: $scope.functionID,
									new_edit: loopThrough[i].parameter_name,
									reason: loopThrough[i].parameter_description
								},
								headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
							});
							promise.then(function(successResponse) {
								console.log(successResponse);
								$scope.data.newReturns.splice(i, 1);
							}, function(errorResponse) {
								alert('Error posting parameter');
							});
						}
					}
				}
			}
			else {
				ngDialog.open({
					template: 'html/ngDialog/not_enough_rep.html'
				});
			}
		}
	}

	$scope.init = function(functionID, userID, votedSuper) {
		$scope.userID = userID;
		$scope.functionID = functionID;
		$scope.data.votedSuperID = votedSuper;

		if ($scope.userID != 'None') {
			var promiseGetUserRep = $http({
				method: "post",
				url: getUserReputation,
				data: {
					user_id: $scope.userID
				},
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
			}).then(function(successResponse) {
				console.log(successResponse);
				$scope.reputation = successResponse.data.points;
			}, function(errorResponse) {
				console.log('Error getting rep');
			})
		}

		var promiseGetSuperCategories = $http({
			method: "post",
			url: getSuperCategories,
			data: {
				dev: true
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});

		promiseGetSuperCategories.then(function(successResponse) {
			console.log(successResponse);
			$scope.data.superCategories = successResponse.data;
			if (votedSuper) {
				//There was already a vote, figure out what category it was
				for (var i= 0; i < $scope.data.superCategories.length; i++) {
					if ($scope.data.superCategories[i].super_id == votedSuper) {
						$scope.data.selectedSuperDescription = $scope.data.superCategories[i].description;
						$scope.data.showSelect = false;
						break;
					}
				}			
			}

			if (!$scope.data.selectedSuperDescription) {
				$scope.data.showSelect = true;
			}
		}, function(errorResponse) {
			//alert('Eror fetching super categories');
		});

		var promiseGetRelatedTranslations = $http({
			method: "post",
			url: getRelatedTranslations,
			data: {
				function_id: $scope.functionID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		}).then(function(successResponse) {
			$scope.data.relatedTranslations = successResponse.data;
			console.log(successResponse);
		}, function(errorResponse) {
			//alert('Error enumerating related translations');
		})


/*		$scope.languageFunction = JSON.parse(functionObject);
		$scope.syntax = $scope.languageFunction.syntax;
		console.log($scope.languageFunction);*/

		var promiseGetFunctionParameters = $http({
			method: "post",
			url: getFunctionParameters,
			data: {
				function_id: $scope.functionID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});

		promiseGetFunctionParameters.then(function(successResponse) {
			$scope.functionParameters = successResponse.data.parameters;
			$scope.functionReturns = successResponse.data.returns;
		}, function(errorResponse) {
			//alert('Error fetching params');
		});

		//Promise to fetch the related function notes to this note
		var promiseGetFunctionNotes = $http({
			method: "post",
			url: getFunctionNotes,
			data: {
				function_id: $scope.functionID
			},
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
		});

		promiseGetFunctionNotes.then(function(successResponse) {
			console.log(successResponse);
			$scope.data.functionNotes = successResponse.data;

			for (var i = 0; i < $scope.data.functionNotes.length; i++) {
				if ($scope.data.functionNotes[i].upvoted) {
					$scope.data.functionNotes[i].upvoteStyle = {
						'color': 'blue'
					};
					continue;
				}
				if ($scope.data.functionNotes[i].downvoted) {
					$scope.data.functionNotes[i].downvoteStyle = {
						'color': 'blue'
					}
				}
			}
		}, function(errorResponse) {
			//alert('Error fetching function notes');
		});
	}

	$scope.editParameters = function(type) {
		if ($scope.userID == 'None') {
			ngDialog.open({
				template: 'html/ngDialog/login_edit.html'
			});
		}
	}
	
}]);