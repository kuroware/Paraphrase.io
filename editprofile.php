<?php
function __autoload($class_name) {
	/*
	Last chance for PHP script to call a class name
	 */
	$class_name = ($class_name == 'OutgoingTranslation' || $class_name == 'IncomingTranslation') ? 'Translate' : $class_name;
	require_once $_SERVER['DOCUMENT_ROOT']. "/includes/$class_name.php";
}
require_once 'navbar.php';
require_once __DIR__ . '/vars/constants.php';
/*require_once 'includes/user.php';
require_once 'vars/constants.php';
require_once 'includes/database.php';*/

$error_id = 0;
$user_id = User::get_current_user_id();
if ($user_id == 'None') {
	die('Could not load this page');
}
else {
	if (isset($_POST['submit'])) {
		$mysqli = Database::connection();
		if (in_array($_FILES['userfile']['type'], $image_restrictions)) {
			//Grab the extension of the submitted file
			switch($_FILES['userfile']['type']) {
				case 'image/jpeg':
					$ext = '.jpg';
					break;
				case 'image/png':
					$ext = '.png';
					break;
			}
			if (!empty($ext)) {
				//The image is inside the acceptable formats, try to parse it
				$target_dir = AVATAR_DIR;
				$filename = tempnam($target_dir, ''); //Unique filename created
				unlink($filename); //Delete the unique file that was created
				$endname = basename($filename, '.tmp') . $ext;
				$target_out = AVATAR_DIR . $endname;
				$temp_name = basename($_FILES['userfile']['tmp_name'], '.tmp') . $ext;
				if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
				//	echo  'Uploaded';
					$move_result = move_uploaded_file($_FILES['userfile']['tmp_name'], $target_out);
					if ($move_result) {
						//If the file was sucessfully uploaded...., begin scaling
						switch($_FILES['userfile']['type']) {
							case 'image/jpeg':
								$source_image = imagecreatefromjpeg($target_out);
								break;
							case 'image/png':
								$source_image = imagecreatefrompng($target_out);
								break;
							case 'image/gif':
								$source_image = imagecreatefromgif($target_out);
								break;
						}										
						$source_imagex = imagesx($source_image);
						$source_imagey = imagesy($source_image);
						$dest_imagex = AVATAR_IMAGE_WIDTH;
						$ratio = $dest_imagex / $source_imagex;
						$dest_imagey = $ratio * $source_imagey;
						$dest_image = imagecreatetruecolor($dest_imagex, $dest_imagey);
						$image = imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $dest_imagex, $dest_imagey, $source_imagex, $source_imagey);
						if ($image) {
								switch($_FILES['userfile']['type']) {
								case 'image/jpeg':
									imagejpeg($dest_image, $target_out, 120);
									break;
								case 'image/png':
									imagepng($dest_image, $target_out, 9);
									break;
								case 'image/gif':
									imagegif($dest_image, $target_out);
									break;
							}
							imagedestroy($source_image);
							imagedestroy($dest_image);

							//Delete the old profile picture
							$sql = "SELECT avatar FROM users WHERE user_id = '$user_id'";
							$result = $mysqli->query($sql)
							or die ($mysqli->error);
							$name = mysqli_fetch_row($result)[0];
							if ($name != 'default.png') {
								unlink(AVATAR_DIR . $name);
							}

							//Update the new one
							$sql = "UPDATE users SET avatar = '$endname' WHERE user_id = '$user_id' LIMIT 1";
							$result = $mysqli->query($sql)
							or die ($mysqli->error);
						}
						else {
							$error_id = 12;
							unlink($target_out);
							imagedestroy($source_image);
							imagedestroy($dest_image);
						}
					}
					else {
						$error_id = 11;
					}
				}
				else {
					$error_id = 13;
				}
			}
			else {
				$error_id = 13;
			}
		}
	}
}
?>
<html ng-app="app">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" href="css/basic.css">
		<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog.css">
		<link rel="stylesheet" type="text/css" href="css/ngDialog/ngDialog-theme-default.css">
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/profile.css">
	</head>
	<body ng-controller="main" ng-init="init('<?php echo $user_id;?>', '<?php echo $error_id;?>')" class="container-fluid col-md-11 col-md-offset-1">
		<div class="col-md-10" style="margin-top:10px;">
			<h2 style="display:inline;margin-left:0px;">{{profile.username}}</h2>
		</div>
		<div class="col-md-1">
			<a ng-href="profile.php?id={{profileID}}" class="btn btn-info" style="float:right;" aria-label="Left Align">View Profile<span class="glyphicon glyphicon-pencil" aria-hidden="true" style="margin-left:5px;"></span></a>
		</div>
		<div class="col-md-11" style="padding-left:0px;">
			<div class="col-md-2">
				<img ng-src="{{profile.avatar}}">
				<h4>About</h4>
				<p style="float:left;line-height:5px;">
					<h5 class="leftbar" style="margin-top:0px;line-height:140%">
					Title
					<br/>
					Reputation
					<br/>
					Profile Views
					<br/>
					Date Joined
					</h5>
				</p>
				<p style="text-align:right;line-height:140%">
					AI
					<br/>
					{{profile.points}}
					<br/>
					{{profile.views}}
					<br/>
					{{profile.date_joined}}
				</p>
				<div style="clear:both;"></div>
				<h4>Stats</h4>
				<p style="float:left;line-height:5px;">
					<h5 class="leftbar" style="margin-top:0px;line-height:140%">
					Upvotes
					<br/>
					Downvotes
					<br/>
					U/D Ratio
					<br/>
					Answers
					<br/>
					Requests
					</h5>
				</p>
				<p style="text-align:right;line-height:140%">
					{{profile.upvotes}}
					<br/>
					{{profile.downvotes}}
					<br/>
					{{profile.ud_ratio | limitTo:4}}
					<br/>
					{{profile.answers}}
					<br/>
					{{profile.requests}}
				</p>
			</div>
			<div class="col-md-9">
				<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
					<fieldset>
						<legend>Profile Picture</legend>
						<div class="form-group">
							<label for="profilePicture" class="col-md-3 control-label">Profile Picture</label>
							<div class="col-md-5">
								<input type="file" name="userfile" id="profilePicture" class="form-control">
								<p class="help-block">
									Image size must be below 1 MB. Optimal width is 150 x 150 (any larger will be scaled down)
								</p>
								<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-5 col-md-offset-3">
								<input type="submit" name="submit" value="Change Profile Picture" class="btn btn-primary">
							</div>
						</div>					
					</fieldset>
				</form>
				<form class="form-horizontal">
					<fieldset>
						<legend>Profile Info</legend>
						<div class="form-group">
							<label for="email" class="col-md-3 control-label">Email</label>
							<div class="col-md-8">
								<input type="email" name="email" ng-model="profile.email" class="form-control">
								<p class="help-block">
									For recovering passwords and other information
								</p>
							</div>
						</div>
						<div class="form-group">
							<label for="from" class="col-md-3 control-label">From</label>
							<div class="col-md-8">
								<input type="text" name="from" ng-model="profile.location" class="form-control">
								<p class="help-block">
									My room, Earth
								</p>
							</div>
						</div>
						<div class="form-group">
							<label for="summary" class="col-md-3 control-label">Description</label>
							<div class="col-md-8">
								<textarea class="form-control" rows="10" ng-model="profile.description"></textarea>
								<p class="help-block">
									Any more words?
								</p>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-5 col-md-offset-3">
								<input type="submit" name="submit" ng-click="editUser()" value="Update Changes" class="btn btn-primary">
							</div>
						</div>		
					</fieldset>
				</form>
				<form class="form-horizontal">
					<fieldset>
						<legend>Related Accounts</legend>
						<div class="form-group">
							<label for="stackoverflow" class="col-md-3 control-label">StackExchange Account</label>
							<div class="col-md-8">
								<input type="text" ng-model="profile.stack_exchange" class="form-control">
								<p class="help-block">
									Please provide a link to your StackExchange account. Will eventually be done through OAuth
								</p>
							</div>
						</div>
						<div class="form-group">
							<label for="github" class="col-md-3 control-label">Github Account</label>
							<div class="col-md-8">
								<input type="text" ng-model="profile.github" class="form-control">
								<p class="help-block">
									Please provide a link to your Github account. Will eventually be done through OAuth
								</p>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-5 col-md-offset-3">
								<input type="submit" name="submit" value="Update Accounts" class="btn btn-primary" ng-click="editUserLinks()">
							</div>
						</div>		
					</fieldset>
				</form>
			</div>
		</div>
	</body>
</html>
<script src="dependencies/angularJS/angular.min.js"></script>
<script src="dependencies/angular-bootstrap/ui-bootstrap-tpls-0.13.0.min.js"></script>
<script src="dependencies/ngDialog-master/js/ngDialog.min.js"></script>
<script src="angular/editprofile/editprofile.js"></script>