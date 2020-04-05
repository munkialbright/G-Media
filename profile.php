<?php include("inc/header.inc.php"); ?>

<?php 

	if (isset($_GET['u'])) {
		$username = mysqli_real_escape_string($conn,$_GET['u']);
		if (ctype_alnum($username)) {
		 	//check user exists
			$check = mysqli_query($conn,"SELECT username, first_name FROM users WHERE username='$username'");
			if (mysqli_num_rows($check)===1) {
				$get = mysqli_fetch_assoc($check);
				$username = $get['username'];
				$firstname = $get['first_name'];	
			}
			else
			{	
				exit("<center><strong>Either this user doesn't exist in our database or the account has been closed. <a href='index.php' style='text-decoration: none;'>Sign Up?</a></strong></center>");
			}
		}
	}

	$post = htmlspecialchars(@$_POST['post']);
	if ($post != "") {

		$date_added = date("Y-m-d H:i:s");

		if (!empty($_FILES['photo'])) {
			if ((($_FILES["photo"]["type"] == "image/jpeg") || (@$_FILES["photo"]["type"] == "image/png") || (@$_FILES["photo"]["type"] == "image/gif")) && (@$_FILES["photo"]["size"] <= 1048576 /*1Mb*/)) 
			{
				$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
				$rand_dir_name = substr(str_shuffle($chars), 0, 15);
				mkdir("userdata/user_photos/$rand_dir_name");

				if (file_exists("userdata/user_photos/$rand_dir_name/".@$_FILES["photo"]["name"])) {
					echo @$_FILES["photo"]["name"]."Already exists";
				}
				else
				{
					move_uploaded_file(@$_FILES["photo"]["tmp_name"], "userdata/user_photos/$rand_dir_name/".@$_FILES["photo"]["name"]);

					//echo "Uploaded and stored in: ".@$_FILES["photo"]["name"];
					$photo_name = @$_FILES["photo"]["name"];

					$image = 'http://localhost/Social%20Media%20Website/Social%20Media/userdata/user_photos/'.$rand_dir_name.'/'.$photo_name;

					$photo_query = mysqli_query($conn, "INSERT INTO photos (`id`, `pid`, `username`, `date_posted`, `album_title`, `image_url`, `removed`) VALUES ('','$user','$user','$date_added','$username','$image','no')");
				}
			}
			elseif (@$_FILES["photo"]["size"] == 0 /*0Mb*/) {
				$image = "";
			}
			else
			{
				echo "Sorry, you can only upload an image of file formate jpeg/png/gif and of size 1Mb maximum...";
			}
		}
		
		if (!empty(@$_POST['post_to'])) {
		    $user_posted_to = @$_POST['post_to'];
		}
		else{
			$user_posted_to = $username;
		}

		$added_by = $user;

		$sqlCommand = "INSERT INTO posts (`id`, `body`, `image`, `date_added`, `added_by`, `user_posted_to`, `deleted`) VALUES ('', '$post', '$image', '$date_added', '$added_by', '$user_posted_to', 'no')";
		$query = mysqli_query($conn,$sqlCommand);
	}

	//check whether the user has uploaded a profile pic or not
	$check_pic = mysqli_query($conn, "SELECT profile_pic FROM users WHERE username = '$username'");
	$get_pic_row = mysqli_fetch_assoc($check_pic);
	$profilepic_db = $get_pic_row['profile_pic'];

	if ($profilepic_db == "") {
		$profilepic = "img/default_pic.jpg";
	}
	else
	{
		$profilepic = "userdata/profile_pics/".$profilepic_db;
	}

	$errorMsg = "";

	if (isset($_POST['addfriend'])) {
		$friend_request = $_POST['addfriend'];
		$user_to = $user;
		$user_from = $username;
		$date_sent = date("Y-m-d H:i:s");

		if ($user_to == $username) {
			$errorMsg = "You can't send a friend request to yourself!";
		}
		else
		{
			// Check if friend request already exists
			$rqt_check = mysqli_query($conn, "SELECT user_to AND user_from FROM friend_requests WHERE (user_to = '$user_to' AND user_from = '$user_from') OR (user_to = '$user_from' AND user_from = '$user_to')");
			$request_check = mysqli_num_rows($rqt_check);
			if ($request_check == 0) {
				$create_request = mysqli_query($conn, "INSERT INTO friend_requests VALUES ('','$user_to','$user_from','$date_sent')");
				$errorMsg = "Your Friend Request has been sent!";
			}
			else
			{
				$errorMsg = "The Friend Request had already been sent!";
			}
		}
	}

?>
 <link rel="stylesheet" type="text/css" href="css/profile.css">

 <div id="profile_wrapper">
 	<div class="float_l">
	 	<center><a href="<?php echo $profilepic; ?>"><img src="<?php echo $profilepic; ?>" height="280" width="225" alt="<?php echo($username); ?>'s Profile" title="<?php echo($username); ?>'s Profile"></a></center>
		<br>
		<form action="<?php echo $username; ?>" method="POST">
			<?php echo $errorMsg; ?>
			<div>

				<?php 

					$friendArray = "";
					$countFriends = "";
					$friendArray12 = "";
					$addAsFriend = "";
					$selectFriendsQuery = mysqli_query($conn, "SELECT friend_array FROM users WHERE username = '$username'");
					$friendRow = mysqli_fetch_assoc($selectFriendsQuery);
					$friendArray = $friendRow['friend_array'];
					if ($friendArray != "") {
						$friendArray = explode(",", $friendArray);
						$countFriends = count($friendArray);
						$friendArray12 = array_slice($friendArray, 0, 12);
					
						$i = 0;
						if (in_array($user, $friendArray)) {
							$addAsFriend = '<input type="submit" name="removefriend" value="Unfriend">';
							$msgFriend = '<input type="submit" name="sendmsg" value="Message" class="go_r">';
							echo $addAsFriend."".$msgFriend;
						}
						elseif ($user !== $username) {
							$addAsFriend = '<input type="submit" name="addfriend" value="Add Friend">';
							echo $addAsFriend;
						}
						else
						{
							//Do Nothing
						}

					}
					else
					{

						if ($user !== $username) {
							$addAsFriend = '<input type="submit" name="addfriend" value="Add Friend">';
							echo $addAsFriend;
						}

					}

					//$user = logged in user
					//$username = user who owns profile
					if (@$_POST['removefriend']) {
						//friend_array for logged in user
						$add_friend_check = mysqli_query($conn, "SELECT friend_array FROM users WHERE username = '$user'");
						$get_friend_row = mysqli_fetch_assoc($add_friend_check);
						$friend_array = $get_friend_row['friend_array'];
						$friend_array_explode = explode(",", $friend_array);
						$friend_array_count = count($friend_array_explode);

						//friend_array for user who owns profile 
						$add_friend_check_username = mysqli_query($conn, "SELECT friend_array FROM users WHERE username = '$username'");
						$get_friend_row_username = mysqli_fetch_assoc($add_friend_check_username);
						$friend_array_username = $get_friend_row_username['friend_array'];
						$friend_array_explode_username = explode(",", $friend_array_username);
						$friend_array_count_username = count($friend_array_explode_username);

						$usernameComma = ",".$username;
						$usernameComma2 = $username.",";

						$userComma = ",".$user;
						$userComma2 = $user.",";

						$replace = "";
						$friend1 = "";

						//Remove user who owns profile from logged in user's friend_array
						if (strstr($friend_array, $usernameComma)) {
							$friend1 = str_replace($usernameComma, $replace, $friend_array);
						}
						elseif (strstr($friend_array, $usernameComma2)) {
							$friend1 = str_replace($usernameComma2, $replace, $friend_array);
						}
						elseif (strstr($friend_array, $username)) {
							$friend1 = str_replace($username, $replace, $friend_array);
						}
						else
						{
							//Do nothing
						}
						$friend2 = "";
						//Remove logged in user from other persons friend_array
						if (strstr($friend_array_username, $userComma)) {
							$friend2 = str_replace($userComma, $replace, $friend_array_username);
						}
						elseif (strstr($friend_array_username, $userComma2)) {
							$friend2 = str_replace($userComma2, $replace, $friend_array_username);
						}
						elseif (strstr($friend_array_username, $user)) {
							$friend2 = str_replace($user, $replace, $friend_array_username);
						}
						else
						{
							//Do Nothing
						}

						$removeFriendQuery = mysqli_query($conn, "UPDATE users SET friend_array = '$friend1' WHERE username = '$user'");
						$removeFriendQuery_username = mysqli_query($conn, "UPDATE users SET friend_array = '$friend2' WHERE username = '$username'");

						echo "Friend Removed...";
						header("Location: $username");
					}

					if (isset($_POST['sendmsg'])) {
						header("Location: my_messages.php?u=$username");
					}
			 	?>
			</div>
		</form>
		<div class="textHeader"><?php echo $user; ?>'s Profile</div>
		<div class="profileLeftSideContent">
			<?php 
				$info_query = mysqli_query($conn,"SELECT * FROM users WHERE username='$username'");
				$user_info = mysqli_fetch_assoc($info_query);
				$fname = $user_info['first_name'];
				$lname = $user_info['last_name'];
				$sex = $user_info['gender'];
				$email = $user_info['email'];
				$about_u = $user_info['bio'];

				$online_query = mysqli_query($conn,"SELECT * FROM online_status WHERE username='$username'");
				$online_info = mysqli_fetch_assoc($online_query);
				$status = $online_info['status'];
				$time = $online_info['time'];

				if ($status == '0') {
					$online = 'Online';
				}
				elseif ($status == '1') {
					$online = 'Last seen on '.$time;
				}
				else {
					$online = '';
				}

				echo "<em>Name</em>:<br><strong>$fname $lname</strong><br><br>";
				echo "<em>Gender</em>:<br><strong>$sex</strong><br><br>";
				echo "<em>Email</em>:<br><strong>$email</strong><br><br>";
				echo "<em>Status</em>:<br><strong>$online</strong><br><br>";
				echo "<em>About Me</em>:<br><strong>$about_u</strong>";
			 ?>
			 <br>
			 <form action="<?php echo 'view_albums.php?u='.$username; ?>" method="POST">
					<input type="submit" name="view_albums" value="View Albums">
			 </form>
		</div>
		<div class="textHeader"><?php echo $user; ?>'s Friends</div>
		<div class="profileLeftSideContent">

			<?php 
				echo '<div class="profileFriends">';
				if ($countFriends != 0) {
					foreach ($friendArray12 as $key => $value) {

						$i++;
						$getFriendQuery = mysqli_query($conn, "SELECT * FROM users WHERE username='$value' LIMIT 1");
						$getFriendRow = mysqli_fetch_assoc($getFriendQuery);
						$friendUsername = $getFriendRow['username'];
						$friendProfilePic = $getFriendRow['profile_pic'];

						if (empty($friendProfilePic)) {
							$friendProfilePic = 'default/default_pic.jpg';
						}

						echo "<a href='$friendUsername'><img src='userdata/profile_pics/$friendProfilePic' alt=\"$friendUsername's Profile\" title=\"$friendUsername's Profile\" height='50' width='40' style='padding-right: 6px;'></a>";
					}

				}
				else
				{
					echo $username." has no friends.";
				}
				echo '</div>';
			 ?>
		</div>
	</div>
 	<div class="float_r">
 		<div class="postForm">
 			<form class="post_form" action="<?php echo $username ?>" method="POST" enctype="multipart/form-data">
				<textarea id="post" name="post" rows="3" cols="80" class="post_l" maxlength="255" placeholder="Type in your post here..." required></textarea><br>
				<input type="file" name="photo" title="Only accept images">
				<input type="radio" name="post_to" value="Public">Public
				<input type="radio" name="post_to" value="Friends">Friends Only<br>
				<input type="submit" name="send" value="Post" style="width: 100%;">
			</form>
 		</div>
		<div class="profilePosts">
			<form action="<?php echo $username ?>" method="POST">
				<?php 

					if (@$_POST['more']) {
						$getposts = mysqli_query($conn,"SELECT * FROM posts WHERE (added_by='$username' OR user_posted_to = '$username') AND deleted = 'no' ORDER BY id DESC");
					}
					else{
						$getposts = mysqli_query($conn,"SELECT * FROM posts WHERE (added_by='$username' OR user_posted_to = '$username') AND deleted = 'no' ORDER BY id DESC LIMIT 10");
					}
					//check if any post exist
					$getposts_check = mysqli_num_rows($getposts);
					if ($getposts_check > 0 && $getposts_check < 10) {
						while ($row = mysqli_fetch_assoc($getposts)) {
							$id = $row['id'];
							$body = $row['body'];	
							$date_added = $row['date_added'];
							$added_by = $row['added_by'];
							$user_posted_to = $row['user_posted_to'];
							$image = $row['image'];
							$reported = $row['reported'];

							$get_cmt_query = mysqli_query($conn, "SELECT post_id FROM post_comments WHERE post_id='$id' AND post_removed='0'");
							$get_cmt = mysqli_fetch_assoc($get_cmt_query);
							$cmt_numrows = mysqli_num_rows($get_cmt_query);

							$get_likes_query = mysqli_query($conn, "SELECT post_id FROM likes WHERE post_id='$id' AND unlike='0'");
							$get_likes = mysqli_fetch_assoc($get_likes_query);
							@$likes_id = $get_likes['id'];
							$likes_numrows = mysqli_num_rows($get_likes_query);

							$get_unlikes_query = mysqli_query($conn, "SELECT post_id FROM likes WHERE post_id='$id' AND unlike='1'");
							$get_unlikes = mysqli_fetch_assoc($get_unlikes_query);
							@$unlikes_id = $get_unlikes['id'];
							$unlikes_numrows = mysqli_num_rows($get_unlikes_query);

							if ($image != "") {
								$pic = "<br><br><center><a href='$image'><img src='$image' alt='' height='280' width='255' title='Click to view image in full size'></a></center>";
							}
							else
							{
								$pic = "";
							}

							if (isset($_POST['like_'.$id])) {

								$like_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','0')";

								//check if user had unliked
								$check_likes = mysqli_query($conn, "SELECT * FROM likes WHERE post_id='$id'");
								while ($likes = mysqli_fetch_assoc($check_likes)) {

									$user_liked = $likes['user_liked'];
									$like_id = $likes['id'];
									if ($user == $user_liked) {
										$like_sql = "UPDATE likes SET unlike = '0' WHERE id = '$like_id'";
										
									}
									else
									{
										$like_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','0')";
										
									}
								}

								$like = mysqli_query($conn, $like_sql);
								echo "Your like has been noted";

							}

							if (isset($_POST['unlike_'.$id])) {

								$unlike_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','1')";

								//check if user had liked
								$check_likes = mysqli_query($conn, "SELECT * FROM likes WHERE post_id='$id'");
								while ($likes = mysqli_fetch_assoc($check_likes)) {

									$user_liked = $likes['user_liked'];
									$like_id = $likes['id'];
									if ($user == $user_liked) {
										$unlike_sql = "UPDATE likes SET unlike = '1' WHERE id = '$like_id'";
									}
									else
									{
										$unlike_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','1')";
									}
								}

								$unlike = mysqli_query($conn, $unlike_sql);
								echo "Your unlike has been noted";
							}

							if (@$_POST['delete_'.$id.'']) {
								$delete_post_query = mysqli_query($conn, "UPDATE posts SET deleted = 'yes' WHERE id = '$id'");
								echo "Post successfully deleted! Please refresh the page to take effect...";
							}

							if (@$_POST['report_'.$id.'']) {
								
								$report_date = date("Y-m-d H:i:s");

								//check if user had reported before
								$check_report = mysqli_query($conn, "SELECT * FROM report WHERE post_id='$id'");
								$report_rows = mysqli_num_rows($check_report) + 1;
								$report_sql = "INSERT INTO report (`id`,`username`,`post_id`,`date`) VALUES ('','$user','$id','$report_date')";
								$report_msg = "The post has been reported!";

								while ($get_report = mysqli_fetch_assoc($check_report)) {
									$reported_by = $get_report['username'];
									if ($user != $reported_by) {
										//Do Nothing
									}
									else
									{
										$report_msg = "You've already reported this post before and your report is being processed.";
										$report_rows -= 1;
										$report_sql = "UPDATE posts SET reported = '$report_rows' WHERE id = '$id'";
									}
								}

								$report = mysqli_query($conn, $report_sql);
								$report_sql = "UPDATE posts SET reported = '$report_rows' WHERE id = '$id'";
								$report_query = mysqli_query($conn,$report_sql );
								echo $report_msg;
							}
							
				?>

			<script type="text/javascript">
				function toggle<?php echo $id; ?>() {
					var elt = document.getElementById("togglecmt<?php echo $id; ?>");
					var text = document.getElementById("displaycmt<?php echo $id; ?>");

					if (elt.style.display == "block") {
						elt.style.display = "none";
						
					}
					else
					{
						elt.style.display = "block";
						
					}
				}
			</script>
						
				<?php 

							if ($added_by == $user) {
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								echo "<div class='s_Posts'>";

								echo "<form action='$user' method='POST'>
									<div style='float: left;'>
										<a href='$added_by'><img src='userdata/profile_pics/$profilepic_info' alt=\"$added_by\" title=\"$added_by's Account\" height='40' width='30' style='border-radius: 50%;'></a>
									</div>
									<div class='posted_by' style='margin-left: 2.5px'>
										Posted by: <a href='$added_by'>$added_by</a>, 
										Posted to: <a href='$user_posted_to'>$user_posted_to</a>, 
										on $date_added
									</div>
									<input type='submit' name='delete_$id' value='X' title='Delete Post' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
									<br><br><br>
									<div style='max-width: 100%; margin-left: 5px;'>
										$body
										$pic
									</div>
									<input type='submit' name='like_$id' value='   ' title='Like' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/like.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($likes_numrows)</span>
									<input type='submit' name='unlike_$id' value='   ' title='Unlike' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/unlike.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($unlikes_numrows)</span>
									<input type='button' value='  ' title='Comment' id='displaycmt$id' onclick='javascript:toggle$id();' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/comment.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($cmt_numrows)</span>
									<div id='togglecmt$id' style='display: none; margin-left: 10px'>
										<iframe src='./comment_frame.php?id=$id' style='max-height: 150px; width: 100%; min-height: 10px;' frameborder='0'></iframe>
									</div>
								</form>
								";

								echo "</div>";
							}
							else
							{
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								if (empty($profilepic_info)) {
									$profilepic_info = 'default/default_pic.jpg';
								}

								echo "<div class='s_Posts'>";

								echo "<form action='$user' method='POST'>
										<div style='float: left;'>
											<a href='$added_by'><img src='userdata/profile_pics/$profilepic_info' alt=\"$added_by\" title=\"$added_by's Account\" height='40' width='30' style='border-radius: 50%;'></a>
										</div>
										<div class='posted_by' style='margin-left: 2.5px'>
											Posted by: <a href='$added_by'>$added_by</a>, 
											Posted to: <a href='$user_posted_to'>$user_posted_to</a>, 
											on $date_added
										</div><br><br><br>
										<div style='max-width: 100%; margin-left: 5px;'>
											$body
											$pic
										</div>
										<input type='submit' name='like_$id' value='   ' title='Like' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/like.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($likes_numrows)</span>
										<input type='submit' name='unlike_$id' value='   ' title='Unlike' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/unlike.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($unlikes_numrows)</span>
										<input type='button' value='  ' title='Comment' id='displaycmt$id' onclick='javascript:toggle$id();' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/comment.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($cmt_numrows)</span>
										<input type='submit' name='report_$id' value='Report' title='Report Post' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209); margin-right: 5px;'>
										<div id='togglecmt$id' style='display: none; margin-left: 10px'>
											<iframe src='./comment_frame.php?id=$id' style='max-height: 150px; width: 100%; min-height: 10px;' frameborder='0'></iframe>
										</div>
									</form>
								";

								echo "</div>";
							}
						}
					}
					elseif ($getposts_check == 10) {
						while ($row = mysqli_fetch_assoc($getposts)) {
							$id = $row['id'];
							$body = $row['body'];	
							$date_added = $row['date_added'];
							$added_by = $row['added_by'];
							$user_posted_to = $row['user_posted_to'];
							$image = $row['image'];

							$get_cmt_query = mysqli_query($conn, "SELECT post_id FROM post_comments WHERE post_id='$id' AND post_removed='0'");
							$get_cmt = mysqli_fetch_assoc($get_cmt_query);
							$cmt_numrows = mysqli_num_rows($get_cmt_query);

							$get_likes_query = mysqli_query($conn, "SELECT post_id FROM likes WHERE post_id='$id' AND unlike='0'");
							$get_likes = mysqli_fetch_assoc($get_likes_query);
							@$likes_id = $get_likes['id'];
							$likes_numrows = mysqli_num_rows($get_likes_query);

							$get_unlikes_query = mysqli_query($conn, "SELECT post_id FROM likes WHERE post_id='$id' AND unlike='1'");
							$get_unlikes = mysqli_fetch_assoc($get_unlikes_query);
							@$unlikes_id = $get_unlikes['id'];
							$unlikes_numrows = mysqli_num_rows($get_unlikes_query);

							if ($image != "") {
								$pic = "<br><br><center><a href='$image'><img src='$image' alt='' height='280' width='255' title='Click to view image in full size'></a></center>";
							}
							else
							{
								$pic = "";
							}

							if (isset($_POST['like_'.$id])) {

								$like_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','0')";

								//check if user had unliked
								$check_likes = mysqli_query($conn, "SELECT * FROM likes WHERE post_id='$id'");
								while ($likes = mysqli_fetch_assoc($check_likes)) {

									$user_liked = $likes['user_liked'];
									$like_id = $likes['id'];
									if ($user == $user_liked) {
										$like_sql = "UPDATE likes SET unlike = '0' WHERE id = '$like_id'";
										
									}
									else
									{
										$like_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','0')";
										
									}
								}

								$like = mysqli_query($conn, $like_sql);
								echo "Your like has been noted";

							}

							if (isset($_POST['unlike_'.$id])) {

								$unlike_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','1')";

								//check if user had liked
								$check_likes = mysqli_query($conn, "SELECT * FROM likes WHERE post_id='$id'");
								while ($likes = mysqli_fetch_assoc($check_likes)) {

									$user_liked = $likes['user_liked'];
									$like_id = $likes['id'];
									if ($user == $user_liked) {
										$unlike_sql = "UPDATE likes SET unlike = '1' WHERE id = '$like_id'";
									}
									else
									{
										$unlike_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','1')";
									}
								}

								$unlike = mysqli_query($conn, $unlike_sql);
								echo "Your unlike has been noted";
							}

							if (@$_POST['delete_'.$id.'']) {
								$delete_post_query = mysqli_query($conn, "UPDATE posts SET deleted = 'yes' WHERE id = '$id'");
								echo "Post successfully deleted! Please refresh the page to take effect...";
							}

							if (@$_POST['report_'.$id.'']) {
								
								$report_date = date("Y-m-d H:i:s");

								//check if user had reported before
								$check_report = mysqli_query($conn, "SELECT * FROM report WHERE post_id='$id'");
								$report_rows = mysqli_num_rows($check_report) + 1;
								$report_sql = "INSERT INTO report (`id`,`username`,`post_id`,`date`) VALUES ('','$user','$id','$report_date')";
								$report_msg = "The post has been reported!";

								while ($get_report = mysqli_fetch_assoc($check_report)) {
									$reported_by = $get_report['username'];
									if ($user != $reported_by) {
										//Do Nothing
									}
									else
									{
										$report_msg = "You've already reported this post before and your report is being processed.";
										$report_rows -= 1;
										$report_sql = "UPDATE posts SET reported = '$report_rows' WHERE id = '$id'";
									}
								}

								$report = mysqli_query($conn, $report_sql);
								$report_sql = "UPDATE posts SET reported = '$report_rows' WHERE id = '$id'";
								$report_query = mysqli_query($conn,$report_sql );
								echo $report_msg;
							}
				?>

			<script type="text/javascript">
				function toggle<?php echo $id; ?>() {
					var elt = document.getElementById("togglecmt<?php echo $id; ?>");
					var text = document.getElementById("displaycmt<?php echo $id; ?>");

					if (elt.style.display == "block") {
						elt.style.display = "none";
						
					}
					else
					{
						elt.style.display = "block";
						
					}
				}
			</script>
						
				<?php 
							
							if ($added_by == $user) {
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								echo "<div class='s_Posts'>";

								echo "<form action='$user' method='POST'>
									<div style='float: left;'>
										<a href='$added_by'><img src='userdata/profile_pics/$profilepic_info' alt=\"$added_by\" title=\"$added_by's Account\" height='40' width='30' style='border-radius: 50%;'></a>
									</div>
									<div class='posted_by' style='margin-left: 2.5px'>
										Posted by: <a href='$added_by'>$added_by</a>, 
										Posted to: <a href='$user_posted_to'>$user_posted_to</a>, 
										on $date_added
									</div>
									<input type='submit' name='delete_$id' value='X' title='Delete Post' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
									<br><br><br>
									<div style='max-width: 100%; margin-left: 5px;'>
										$body
										$pic
									</div>
									<input type='submit' name='like_$id' value='   ' title='Like' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/like.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($likes_numrows)</span>
									<input type='submit' name='unlike_$id' value='   ' title='Unlike' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/unlike.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($unlikes_numrows)</span>
									<input type='button' value='  ' title='Comment' id='displaycmt$id' onclick='javascript:toggle$id();' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/comment.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($cmt_numrows)</span>
									<div id='togglecmt$id' style='display: none; margin-left: 10px'>
										<iframe src='./comment_frame.php?id=$id' style='max-height: 150px; width: 100%; min-height: 10px;' frameborder='0'></iframe>
									</div>
								</form>
								";

								echo "</div>";
							}
							else
							{
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								if (empty($profilepic_info)) {
									$profilepic_info = 'default/default_pic.jpg';
								}

								echo "<div class='s_Posts'>";

								echo "<form action='$user' method='POST'>
										<div style='float: left;'>
											<a href='$added_by'><img src='userdata/profile_pics/$profilepic_info' alt=\"$added_by\" title=\"$added_by's Account\" height='40' width='30' style='border-radius: 50%;'></a>
										</div>
										<div class='posted_by' style='margin-left: 2.5px'>
											Posted by: <a href='$added_by'>$added_by</a>, 
											Posted to: <a href='$user_posted_to'>$user_posted_to</a>, 
											on $date_added
										</div><br><br><br>
										<div style='max-width: 100%; margin-left: 5px;'>
											$body
											$pic
										</div>
										<input type='submit' name='like_$id' value='   ' title='Like' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/like.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($likes_numrows)</span>
										<input type='submit' name='unlike_$id' value='   ' title='Unlike' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/unlike.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($unlikes_numrows)</span>
										<input type='button' value='  ' title='Comment' id='displaycmt$id' onclick='javascript:toggle$id();' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/comment.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($cmt_numrows)</span>
										<input type='submit' name='report_$id' value='Report' title='Report Post' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209); margin-right: 5px;'>
										<div id='togglecmt$id' style='display: none; margin-left: 10px'>
											<iframe src='./comment_frame.php?id=$id' style='max-height: 150px; width: 100%; min-height: 10px;' frameborder='0'></iframe>
										</div>
									</form>
								";

								echo "</div>";
							}

						}

						echo "<form action='$username' method='POST'>
								<input type='submit' name='more' value='Show All'>
							</form>
						";
					}
					elseif ($getposts_check > 10) {
						while ($row = mysqli_fetch_assoc($getposts)) {
							$id = $row['id'];
							$body = $row['body'];	
							$date_added = $row['date_added'];
							$added_by = $row['added_by'];
							$user_posted_to = $row['user_posted_to'];
							$image = $row['image'];
							
							$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
							$get_info = mysqli_fetch_assoc($get_user_info);
							$profilepic_info = $get_info['profile_pic'];

							$get_cmt_query = mysqli_query($conn, "SELECT post_id FROM post_comments WHERE post_id='$id' AND post_removed='0'");
							$get_cmt = mysqli_fetch_assoc($get_cmt_query);
							$cmt_numrows = mysqli_num_rows($get_cmt_query);

							$get_likes_query = mysqli_query($conn, "SELECT post_id FROM likes WHERE post_id='$id' AND unlike='0'");
							$get_likes = mysqli_fetch_assoc($get_likes_query);
							@$likes_id = $get_likes['id'];
							$likes_numrows = mysqli_num_rows($get_likes_query);

							$get_unlikes_query = mysqli_query($conn, "SELECT post_id FROM likes WHERE post_id='$id' AND unlike='1'");
							$get_unlikes = mysqli_fetch_assoc($get_unlikes_query);
							@$unlikes_id = $get_unlikes['id'];
							$unlikes_numrows = mysqli_num_rows($get_unlikes_query);

							if ($image != "") {
								$pic = "<br><br><center><a href='$image'><img src='$image' alt='' height='280' width='255' title='Click to view image in full size'></a></center>";
							}
							else
							{
								$pic = "";
							}

							if (isset($_POST['like_'.$id])) {

								$like_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','0')";

								//check if user had unliked
								$check_likes = mysqli_query($conn, "SELECT * FROM likes WHERE post_id='$id'");
								while ($likes = mysqli_fetch_assoc($check_likes)) {

									$user_liked = $likes['user_liked'];
									$like_id = $likes['id'];
									if ($user == $user_liked) {
										$like_sql = "UPDATE likes SET unlike = '0' WHERE id = '$like_id'";
										
									}
									else
									{
										$like_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','0')";
										
									}
								}

								$like = mysqli_query($conn, $like_sql);
								echo "Your like has been noted";

							}

							if (isset($_POST['unlike_'.$id])) {

								$unlike_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','1')";

								//check if user had liked
								$check_likes = mysqli_query($conn, "SELECT * FROM likes WHERE post_id='$id'");
								while ($likes = mysqli_fetch_assoc($check_likes)) {

									$user_liked = $likes['user_liked'];
									$like_id = $likes['id'];
									if ($user == $user_liked) {
										$unlike_sql = "UPDATE likes SET unlike = '1' WHERE id = '$like_id'";
									}
									else
									{
										$unlike_sql = "INSERT INTO likes (`id`,`user_liked`,`post_id`,`unlike`) VALUES ('','$user','$id','1')";
									}
								}

								$unlike = mysqli_query($conn, $unlike_sql);
								echo "Your unlike has been noted";
							}

							if (@$_POST['delete_'.$id.'']) {
								$delete_post_query = mysqli_query($conn, "UPDATE posts SET deleted = 'yes' WHERE id = '$id'");
								echo "Post successfully deleted! Please refresh the page to take effect...";
							}

							if (@$_POST['report_'.$id.'']) {
								
								$report_date = date("Y-m-d H:i:s");

								//check if user had reported before
								$check_report = mysqli_query($conn, "SELECT * FROM report WHERE post_id='$id'");
								$report_rows = mysqli_num_rows($check_report) + 1;
								$report_sql = "INSERT INTO report (`id`,`username`,`post_id`,`date`) VALUES ('','$user','$id','$report_date')";
								$report_msg = "The post has been reported!";

								while ($get_report = mysqli_fetch_assoc($check_report)) {
									$reported_by = $get_report['username'];
									if ($user != $reported_by) {
										//Do Nothing
									}
									else
									{
										$report_msg = "You've already reported this post before and your report is being processed.";
										$report_rows -= 1;
										$report_sql = "UPDATE posts SET reported = '$report_rows' WHERE id = '$id'";
									}
								}

								$report = mysqli_query($conn, $report_sql);
								$report_sql = "UPDATE posts SET reported = '$report_rows' WHERE id = '$id'";
								$report_query = mysqli_query($conn,$report_sql );
								echo $report_msg;
							}
				?>

			<script type="text/javascript">
				function toggle<?php echo $id; ?>() {
					var elt = document.getElementById("togglecmt<?php echo $id; ?>");
					var text = document.getElementById("displaycmt<?php echo $id; ?>");

					if (elt.style.display == "block") {
						elt.style.display = "none";
						
					}
					else
					{
						elt.style.display = "block";
						
					}
				}
			</script>
						
				<?php 

							if ($added_by == $user) {
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];


								echo "<div class='s_Posts'>";

								echo "<form action='$user' method='POST'>
									<div style='float: left;'>
										<a href='$added_by'><img src='userdata/profile_pics/$profilepic_info' alt=\"$added_by\" title=\"$added_by's Account\" height='40' width='30' style='border-radius: 50%;'></a>
									</div>
									<div class='posted_by' style='margin-left: 2.5px'>
										Posted by: <a href='$added_by'>$added_by</a>, 
										Posted to: <a href='$user_posted_to'>$user_posted_to</a>, 
										on $date_added
									</div>
									<input type='submit' name='delete_$id' value='X' title='Delete Post' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
									<br><br><br>
									<div style='max-width: 100%; margin-left: 5px;'>
										$body
										$pic
									</div>
									<input type='submit' name='like_$id' value='   ' title='Like' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/like.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($likes_numrows)</span>
									<input type='submit' name='unlike_$id' value='   ' title='Unlike' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/unlike.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($unlikes_numrows)</span>
									<input type='button' value='  ' title='Comment' id='displaycmt$id' onclick='javascript:toggle$id();' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/comment.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($cmt_numrows)</span>
									<div id='togglecmt$id' style='display: none; margin-left: 10px'>
										<iframe src='./comment_frame.php?id=$id' style='max-height: 150px; width: 100%; min-height: 10px;' frameborder='0'></iframe>
									</div>
								</form>
								";

								echo "</div>";
							}
							else
							{
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								if (empty($profilepic_info)) {
									$profilepic_info = 'default/default_pic.jpg';
								}

								echo "<div class='s_Posts'>";

								echo "<form action='$user' method='POST'>
										<div style='float: left;'>
											<a href='$added_by'><img src='userdata/profile_pics/$profilepic_info' alt=\"$added_by\" title=\"$added_by's Account\" height='40' width='30' style='border-radius: 50%;'></a>
										</div>
										<div class='posted_by' style='margin-left: 2.5px'>
											Posted by: <a href='$added_by'>$added_by</a>, 
											Posted to: <a href='$user_posted_to'>$user_posted_to</a>, 
											on $date_added
										</div><br><br><br>
										<div style='max-width: 100%; margin-left: 5px;'>
											$body
											$pic
										</div>
										<input type='submit' name='like_$id' value='   ' title='Like' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/like.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($likes_numrows)</span>
										<input type='submit' name='unlike_$id' value='   ' title='Unlike' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/unlike.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($unlikes_numrows)</span>
										<input type='button' value='  ' title='Comment' id='displaycmt$id' onclick='javascript:toggle$id();' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/comment.png); margin: 5px; margin-right: 0px; margin-left: 10px; background-repeat: no-repeat; border-radius: 0px;'> <span style='color: #FFF;'>($cmt_numrows)</span>
										<input type='submit' name='report_$id' value='Report' title='Report Post' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209); margin-right: 5px;'>
										<div id='togglecmt$id' style='display: none; margin-left: 10px'>
											<iframe src='./comment_frame.php?id=$id' style='max-height: 150px; width: 100%; min-height: 10px;' frameborder='0'></iframe>
										</div>
									</form>
								";

								echo "</div>";
							}
						}

						echo "<form action='$username' method='POST'>
								<input type='submit' name='less' value='Show Less'>
							</form>
						";
					}
					else
					{
						echo "<br><em>Your posts goes here...</em><br><hr>";
					}
					
				?>
			</form>
		</div>
 	</div>
	<div class="clr"></div>
 </div>
 <script src="js/main.js" type="text/javascript"></script>
