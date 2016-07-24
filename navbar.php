<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
require_once __DIR__ . '/includes/User.php';
require_once __DIR__ . '/includes/Database.php';
$user_id = User::get_current_user_id();
//Attempt to create the user object
if (is_numeric($user_id)) {
	$mysqli = Database::connection();
	$sql = "SELECT username, points, avatar FROM users WHERE user_id = '$user_id'";
	$result = $mysqli->query($sql)
	or die ($mysqli->error);
	if ($result->num_rows == 1) {
		$x = array('user_id' => $user_id);
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$row = array_merge($x, $row);
		$user = new User($row);
	}
}
?>
<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" ng-init="navCollapsed = true" ng-click="navCollapsed = !navCollapsed">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
    		<a class="navbar-brand" href="#" style="padding-top:8px;">
    			<img alt="Brand" src="ui/orange.ico" width="25px" height="25px">
    		</a>
    		<a class="navbar-brand" href="index.php">Paraphrase</a>
<!--       		<a class="navbar-brand" href="index.php" dropdown dropdown-toggle dropdown-append-to-body>
    			Paraphrase
    			<span class="caret">
    			</span>
    			<ul class="dropdown-menu">
    				<li><a href="#">Contribute</a>
    				</li>
    				<li>
    					<a href="#">Statistics</a>
    				</li>
    			</ul>
    		</a> -->
  		</div>
		<div class="navbar-collapse collapse" id="bs-example-navbar-collapse-1" ng-class="!navCollapsed && 'in'">
			<ul class="nav navbar-nav">
<!-- 				<li>
					<a href="translations.php">Translations</a>
				</li> -->
<!--         		<li dropdown>
					<a href dropdown-toggle dropdown-toggle>
					Posts<span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu">
						<li><a href="posts.php">Posts</a></li>
						<li class="divider"></li>
						<li><a href="post_article.php">Submit Post</a></li>
					</ul>
   	 			</li> -->
<!-- 				<li>
					<a href="translations.php">Translations</a>
				</li> -->
<!-- 			    <li class="dropdown">
        			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Languages<span class="caret"></span></a>
        			<ul class="dropdown-menu">
        				<li><a href="language.php?id=1">PHP</a></li>
        				<li><a href="language.php?id=2">Javascript</a></li>
        			</ul>
        		</li> -->
 <!--        		<li dropdown>
					<a href dropdown-toggle dropdown-toggle>
					Languages<span class="caret"></span>
					</a>
					<ul class="dropdown-menu" role="menu">
        				<li><a href="language.php?id=1">PHP</a></li>
        				<li><a href="language.php?id=2">Javascript</a></li>
        				<li><a href="language.php?id=3">C</a></li>
        				<li><a href="language.php?id=4">Python</a></li>
        				<li><a href="language.php?id=5">Ruby</a></li>
        				<li><a href="language.php?id=6">Swift</a></li>
        				<li><a href="language.php?id=7">Java</a></li>
        				<li><a href="language.php?id=8">C++</a></li>
        				<li><a href="language.php?id=9">C#</a></li>
        				<li><a href="language.php?id=10">AngularJS*</a></li>
					</ul>
   	 			</li> -->
				<li>	
					<a href="index.php?lan=php">PHP</a>
				</li>
				<li>
					<a href="index.php?lan=javascript">Javascript</a>
				</li>
				<li>
					<a href="index.php?lan=python">Python</a>
				</li>
				<li>
					<a href="index.php?lan=ruby">Ruby</a>
				</li>
				<li>
					<a href="pending.php?lan=swift">
						Swift
					</a>
   	 			<li>
   	 				<a href="add.php">Contribute</a>
   	 			</li>
<!-- 				<li>
					<a href="stats.php">Statistics</a>
				</li> -->
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<?php if ($user_id != 'None') { ?>
				<li>
					<a href="profile.php?id=<?php echo $user_id;?>">
						<img src="<?php echo $user->avatar;?>" width="25" height="25" style="margin-right:5px;padding:0px;">
						<?php echo $user->username;?> (<?php echo $user->points;?> rep.)
					</a>
				</li>
				<li>
					<a href="logout.php">Logout</a>
				</li>
				<?php }
				else { ?>
				<li>
					<a href="login.php">Login</a>
				</li>
				<li>
					<a href="login.php?tab=sign">Sign Up</a>
				</li>
				<?php }?>
				<li>
					<a href="updatelog.php">About</a>
				</li>
				<li>
					<a href="contact.php">Contact Us</a>
				</li>
			</ul>
		</div>
	</div>	
</nav>
