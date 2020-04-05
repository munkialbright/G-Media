<?php 
	include ("inc/connect.inc.php");
	session_start();
	if (!isset($_SESSION['user_login'])) {
		$user = "";
	}
	else {
		$user = $_SESSION["user_login"];
	}

	$get_unread_query = mysqli_query($conn, "SELECT opened FROM pvt_messages WHERE user_to = '$user' AND opened = 'no' AND user_from_deleted = 'no' AND user_to_deleted = 'no'");
	$get_unread = mysqli_fetch_assoc($get_unread_query);
	$unread_numrows = mysqli_num_rows($get_unread_query);
	$unread_numrows = "(".$unread_numrows.")";

	$get_request_query = mysqli_query($conn, "SELECT * FROM friend_requests WHERE user_to = '$user'");
	$get_request = mysqli_fetch_assoc($get_request_query);
	$request_numrows = mysqli_num_rows($get_request_query);
	$request_numrows = "(".$request_numrows.")";
 ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link rel="manifest" href="json/manifest.json">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<meta name="description" content="A social media app">
		<meta name="theme-color" content="#eff5f9">
		<link rel="shortcut icon" href="img/G-Media.ico" type="image/x-icon">
		<link rel="apple-touch-icon" href="img/G-Media.ico">
		<script src="http://localhost/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<title>G-Media</title>
	</head>
	<body>

		<div class="headerMenu">
			<div id="user_name">
				<?php 
					if ($user) {

						$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$user'");
						$get_info = mysqli_fetch_assoc($get_user_info);
						$profilepic_info = $get_info['profile_pic'];

						echo "<a href='$user'><img src='userdata/profile_pics/$profilepic_info' height='50' width='40' alt=\"$user\" title=\"$user's Account\"></a>";
					}
				?>
			</div>
			<div id="wrapper">

				<?php 
					if ($user) {
						echo '<center>
								<div class="search_box">
									<form action="search.php" method="GET" id="search" autocomplete="on">
										<input type="text" name="q" size="60" placeholder="Search ..." autocomplete="on" required>
									</form>
								</div>
							</center>';
					}
				?>

				<nav id="menu">
					<?php 
						if (!$user) {
							echo '<div class="topnav" id="myTopnav">
									<a href="about.php" style="padding-right: 40px;">About</a>
									<a href="javascript:void(0);" style="font-size:15px;" class="icon" onclick="myDropdown()">&#9776;</a>
								  </div>';
						}
						else
						{
							echo '<div class="topnav" id="myTopnav">
									<a href="home.php">Home</a>
									<a href="news.php">News</a>
									<a href="'.$user.'">Profile</a>
									<a href="friend_requests.php">Requests '.$request_numrows.'</a>
									<a href="message.php">Inbox '.$unread_numrows.'</a>
									<a href="account_settings.php">Settings</a>
									<a href="about.php">About</a>
									<a href="feedback.php">Feedback</a>
									<a href="logout.php">Logout</a>
									<a href="javascript:void(0);" style="font-size:15px;" class="icon" onclick="myDropdown()">&#9776;</a>
								  </div>';
						}
					?>
					
				</nav>
			</div>
		</div>
		<br>
		<br>
		<br>
		<br>