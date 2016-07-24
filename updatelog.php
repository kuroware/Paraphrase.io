<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	if ($class_name == 'OutgoingTranslation' || 'IncomingTranslation') {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/Translate.php';
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/$class_name.php";
}
require_once 'navbar.php';
/*require_once 'includes/user.php';*/
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/highlightjs/tomorrow.css">
		<link rel="stylesheet" type="text/css" href="css/angucomplete/angucomplete.css">
	</head>
	<body ng-controller="main" class="container-fluid">
		<div class="col-md-10 col-md-offset-1">
			<div class="row">
				<div class="col-md-12">
					<p class="text-center">
						<h3 style="text-align:center;">Paraphrase</h3>
						Paraphrase is an attempt at a community solution to make grasping new programming languages easier and faster by abridging the tedious syntax translation for common concepts and providing relevant details to elaborate on languages functions. Paraphrase strives to hear what the community or anyone who has been on this site has to say. Please feel free to contact us <a href="contact.php">here</a>, whether it's something as simple as an idea, getting involved, or criticism. 
					</p>
					<p class="text-right">
						<small>
							Paraphrase built with: PHP (Server-side language), MySQL (Database), Javascript (Client-side), AngularJS(Client-side), JQuery (Client-side)
							<br/>
							Makes use of: Angucomplete, D3.js, Chart.js, angular-charts, Angular-FusionCharts, Bootstrap, UI Bootstrap, BootstrapGlyphicons
						</small>
					</p>
					<legend>
						Ongoing Projects
					</legend>
					<p>
						<ul>
							<li>
								<strong>Categorization Project</strong>
								<ul>
									<li>Functions and methods from all languages are being categorized on user's votes
									</li>
									<li>
										All votes are automatically collected and the most voted category is automatically assigned to functions
									</li>
								</ul>
							</li>
							<li>
								<strong>User Roles/Moderators/Admins</strong>
								<ul>
									<li>Awaiting for a substantial userbase and enough rep. actions and begin assigning user roles based on internal system and review
									</li>
									<li>
										Implementing user badges and reputation privelges
									</li>
								</ul>
							<li>
								<strong>PHP and AngularJS Re-factoring</strong>
								<ul>
									<li>Major re-factoring of most PHP classes and backend class designs
									</li>
									<li>
										As a lot of class design was changed during development, many php files use the incorrect class design and need to be updated
									</li>
									<li>
										AngularJS formatting and code needs to be standarzied and remove redundant injections
									</li>
								</ul>
							</li>
						</ul>
					</p>
					<legend>
						<h3>Development Goals</h3>
					</legend>
					<strong>Current Development</strong>
					<ul>
						<li>
							Notification System
							<ul>
								<li>Current progress on backend system and class design as well as coordiation with the MySQL storage
								</li>
							</ul>
						</li>
						<li>
							Reputation System
							<ul>
								<li>Main class design and actions are complete for reputation, but notifications for it as well as feedback and a better flow are still being worked on as well as preventing "spammy" reputation (i.e. Post A was un-upvoted and upvoted 10^100 times)
								</li>
							</ul>
						</li>
						<li>
							AngularJS optimization
							<ul>
								<li>
									Faster loading with ng-repeat and filtering (currently O(n) and large constant factors which can be reduced)
								</li>
								<li>
									Discovering un-needed watchers and speeding up DOM rendering
								</li>
								<li>
									Updating dependencies and standardizing everything into `Bower` (currently mixed between custom dependencies and bower)
								</li>
							</ul>
						</li>
						<li>
							Mobile Optimization
							<ul>
								<li>Fixing offset Chart.js library rendering an D3.js offset
								</li>
								<li>Fixing or imporving weird stacking of columns supplied from `Bootstrap`
								</li>
							</ul>
						</li>
						<li>
							Admin and Moderator Dashboard
							<ul>
								<li>
								Implementation of admin and moderator actions on the front-end side instead of very rudimentary PHP scripts
								</li>
								<li>
								Implementation of seperation of different user roles and priveleges and possibly settings. The current DB schema does not support this
								</li>
							</ul>
						</li>
						<li>
							Image Standarization
							<ul>
								<li>Begin standarization of images for avatars and other UI on the site for mobile optimization</li>
								<li>Some parts of the site make use of CSS properities and JS to crop, size, and stretch images while maintaing aspect ratio. This does not bode well with a wide array of possible images and avatars (since avatars must only follow the 150px height rule)
								</li>
							</ul>
						</li>
					</ul>
					<strong>August 30, 2015</strong>
					<ul>
						<li>Launch site</li>
					</ul>
					<strong>September 10, 2015</strong>
					<ul>
						<li>
							Question or post functionality up if community response favors it
						</li>
						<li>
							Refactoring of most PHP framework
						</li>
						<il>
							Re-design of basic UI elements and layout of Paraphrase.io
						</il>
						<li>
							Begin development on inline code annotations
						</li>
						<li>
							Reputation and user roles stabilized and fully brought out. Roles will be assigned tentatively on this date
						</li>
						<li>
							User badges almost done
						</li>
					</ul>
					<strong>September 18, 2015</strong>
					<ul>
						<li>Paraphrase.io becomes open-source
						</li>
					</ul>
				</div>
			</div>
			<legend>
				<h3>Changelog</h3>
			</legend>
			<strong>Latest Update: August 30, 2015</strong>
			<ul>
				<li>
					Patched and disocvered most bugs (logining twice, un-needed watchers, some site flows, PHP error logic)
				</li>
				<li>
					Implemented basic reputation priveleges in the functions
				</li>
				<li>
					Developed system for handling reputation privelge checking
				</li>
				<li>
					Major UI overhaul on the home page and switched the side bar to the right and the tab bar to the left.
				</li>
				<li>
					Spent almost an hour trying to find the classes for tabs and putting in a nice vertical border between the tabs and switching view, but there was inexplicable padding between them creating a weird look when "This Month" was selected. Don't tell anyone, but I did a hacky ng-style and made the vertical border disappear when it was selected to make it merge better
				</li>
			</ul>
			<strong>August 24, 2015</strong>
			<ul>
				<li>Removed the Q&A section code for now</li>
				<li>Removed weighted search and generic search features on the site due to redundancy</li>
				<li>Uploaded Python, Ruby, PHP, and Javascript functions, methods, classes, and services</li>
				<li>Implemented Markdown and preview and HTML sanitiztion and patched up remaining holes in security</li>
				<li>Began code on StackOverflow OAuth</li>
				<li>Modified the xcharts library to slightly patch offset</li>
				<li>Modified template for translate.php</li>
				<li>Prepared site for deployment</li>
				<li>Implemented new blue class for upvotes and downvotes. UI now more intuitive</li>
				<li>Re-added angucomplete-alt on the home page and implemented it to major pages</li>
				<li>Added infinite scrolling to the home page</li>
				<li>Added pagination to reputation and languages.php for the indexed functions</li>
			</ul>
			<strong>August 8, 2015</strong>
			<ul>
				<li>Removed the "posts" section, might be re-implemented later but could not find a good implementation, especially since I'm currently the only user on the site...</li>
				<li>Began some preliminary code on Q&A</li>
				<li>Converted most InnoDB tables to MyISAM, due to its ability to full-text index and with much faster performance</li>
				<li>Added some weights to MySQL's MATCH...AGAINST</li>
			</ul>
			<strong>July 22, 2015</strong>
			<ul>
				<li>FTP'd site from local production to new domain paraphrase.io</li>
				<li>Fixed almost all file's relative path error and using the recommended practice of $_SERVER['DOCUMENT_ROOT'] or __DIR__</li>
				<li>Hax0red a bug with PHP http_response_code not sending the correct status with a non-legit fix. For some reason, Angular kept parsing a PHP's script http status code as "200" instead of 400 even after all HTTP headers were set to 400. Weird, I'll fully fix this later</li>
				<li>Removed the "new" tab since it seems a bit redundant to have it along with the activity tab. Might need to being thinking about how to score the Featured tab</li>
				<li>Normalized all file paths for require_once and dependencies</li>	
				<li>Began using bower for all dependencies...</li>
			</ul>
			<strong>July 20, 2015</strong>
			<ul>
				<li>Decided on a pretty bad reputation data schema and implemented a few PHP methods and integrated it with the new database schema to make reputation viable.
				</li>
				<li>
					Still looking at easy ways to allow editting of functions
				</li>
				<li>
					Also still thinking about how to develop the people engaged and people reached stat.
				</li>
				<li>
					Fixed a few chart.js stuff, still deciding on whether or not to use FusionCharts vs Chart.js. Chart.js has an annoying bug where if it is placed in an hidden element and rendered in there first (like for example an inactive tab in tabset directive for angular-bootstrap), it sets its height to 0px to "hide" it. It doesn't repaint the canvas or reload about selection of the inactive tab though and even the built-in directives such as chart.js destroy() don't work. Used a pretty hax0r way to fix this by setting it outside the tabset though. Also, beware, tabset has its own $scope, for anyone ever using angular-bootstrap.
				</li>
			</ul>
			<strong>July 17, 2015</strong>
			<ul>
				<li>
					Added in `function_parameters` table which keeps track of the parameters a function expects and has a single column called `type` that signifies whether or not the parameter is an expected input or an expected output
					<ul>
						<li>
							*Should be fast selection anyways for now. Not sure if I'm going to create a universal table to avoid overhead costs of calling MySQL. O(log(n)), simple BTREE on parameter_id.
						</li>
						<li>
							** Not sure about adding a 'UNIQUE' constraint on the table, would speed up but data is inflexbile
						</li>
					</ul>
				</li>
				<li>
					Added in the search bar in the translations page, thinking about making the translations page into the home page, not sure where to put statistics though
				</li>
				<li>
					Integrated the stats data schema for translations done today and new translation requests
				</li>
				<li>
					Still not quite sure how I'm going to solve people from upvoting and un-upvoting and then upvoting a translation and basically keeping it at the top whenever. Perhaps instead of when a translation is un-upvoted, I won't execute:
					<pre>
DELETE FROM `foo_table_x` WHERE user_id = 'x' AND function_id = 'y';
					</pre>
					And instead, I'll update the feedback to 0, to store a record that this function was once upvoted by x user:
					<pre>
UPDATE `foo_table_x` SET status = 0 WHERE user_id = 'x' AND function_id = 'y'
					</pre>
				</li>
				<li>
					Started some integration on the edit history of functions and how to make the site more "wiki". This may be difficult as I currently don't have any internet connection for the next couple of days and am devving offline on XAMPP. I'll come back to it later
				</li>
			</ul>
			<strong>July 12, 2015</strong>
			<ul>
				<li>
				For now, just store the function equivalents and build on a full-fledged translator. Output nice explanations and a link to the description of the function
				</li>
				<li>
				<pre>
<strong>Proposed data structure:</strong>
Main_ID		  Description 		Type
1      		makes uppercase       1 - String method

Function_ID    Main_ID       Language       Name
1 				 1             1             strtoupper

Language_ID      Language Name
1                   PHP
			</pre>
			</li>
			</ul>
		</div>
	</body>
</html>
<script src="bower_components/angular/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/highlightjs/highlight.pack.js"></script>
<script src="dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="dependencies/angular-highlight/angular-highlightjs.min.js"></script>
<script src="angular/updateLog/updateLog.js"></script>
