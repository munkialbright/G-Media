<?php 
	include("inc/header.inc.php");
 ?>
<link rel="stylesheet" type="text/css" href="css/profile.css">
<link rel="stylesheet" type="text/css" href="css/home.css">
 
<div class="newsFeed">
	<h2>Newsfeed (<a href="#public">Public</a> / <a href="#friends">Friends</a>)</h2>
</div>

<div class="div_wrapper">

	<div id="public" class="newsfeedPosts">
		 <form action="home.php" method="POST">
			<?php 

				if (@$_POST['more']) {
					$getposts = mysqli_query($conn,"SELECT * FROM posts WHERE user_posted_to = 'Public' AND deleted = 'no' ORDER BY id DESC LIMIT 50");
				}
				else{
					$getposts = mysqli_query($conn,"SELECT * FROM posts WHERE user_posted_to = 'Public' AND deleted = 'no' ORDER BY id DESC LIMIT 10");
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
							$pic = "<br><br><center><a href='$image'><img src='$image' alt='' height='280' width='255' alt='' title='Click to view image in full size'></a></center>";
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
							header("Location: home.php");

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
							header("Location: home.php");
						}

						if (@$_POST['delete_'.$id.'']) {
							$delete_post_query = mysqli_query($conn, "UPDATE posts SET deleted = 'yes' WHERE id = '$id'");
							echo "Post successfully deleted! Please refresh the page to take effect...";
							header("Location: home.php");
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

							echo "<form action='home.php' method='POST'>
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

							echo "<form action='home.php' method='POST'>
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
							header("Location: home.php");

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
							header("Location: home.php");
						}

						if (@$_POST['delete_'.$id.'']) {
							$delete_post_query = mysqli_query($conn, "UPDATE posts SET deleted = 'yes' WHERE id = '$id'");
							echo "Post successfully deleted! Please refresh the page to take effect...";
							header("Location: home.php");
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

							echo "<form action='home.php' method='POST'>
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

							echo "<form action='home.php' method='POST'>
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

					echo "<form action='home.php' method='POST'>
							<input type='submit' name='more' value='Show More' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
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
							header("Location: home.php");

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
							header("Location: home.php");
						}

						if (@$_POST['delete_'.$id.'']) {
							$delete_post_query = mysqli_query($conn, "UPDATE posts SET deleted = 'yes' WHERE id = '$id'");
							echo "Post successfully deleted! Please refresh the page to take effect...";
							header("Location: home.php");
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
						
						$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
						$get_info = mysqli_fetch_assoc($get_user_info);
						$profilepic_info = $get_info['profile_pic'];

						if ($added_by == $user) {
							$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
							$get_info = mysqli_fetch_assoc($get_user_info);
							$profilepic_info = $get_info['profile_pic'];

							echo "<div class='s_Posts'>";

							echo "<form action='home.php' method='POST'>
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

							echo "<form action='home.php' method='POST'>
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

					echo "<form action='home.php' method='POST'>
							<input type='submit' name='less' value='Show Less' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
						</form>
					";
				}
				else
				{

					echo "<div class='s_Posts'>";

					echo "<em>You don't yet have any public newsfeeds.</em><br>";

					echo "</div>";
				}
				
			?>
		</form>
	</div>

	<div id="friends" class="newsfeedPosts">
		 <form action="home.php" method="POST">
			<?php 

				
				$selectFriendsQuery = mysqli_query($conn, "SELECT friend_array FROM users WHERE username = '$user'");
				$friendRow = mysqli_fetch_assoc($selectFriendsQuery);
				$friendArray = $friendRow['friend_array'];
				if ($friendArray != "") {
					$friendArray = explode(",", $friendArray);
					$countFriends = count($friendArray);
					$friendArray12 = array_slice($friendArray, 0);

					$i = 0;

					foreach ($friendArray12 as $key => $value) {
						$i++;
						$getFriendQuery = mysqli_query($conn, "SELECT * FROM users WHERE username='$value' LIMIT 1");
						$getFriendRow = mysqli_fetch_assoc($getFriendQuery);
						$friendUsername = $getFriendRow['username'];
						
						if (@$_POST['more']) {
							$getposts = mysqli_query($conn,"SELECT * FROM posts WHERE user_posted_to = 'Friends' AND added_by = '$friendUsername' AND deleted = 'no' ORDER BY id DESC LIMIT 10");
						}
						else{
							$getposts = mysqli_query($conn,"SELECT * FROM posts WHERE user_posted_to = 'Friends' AND added_by = '$friendUsername' AND deleted = 'no' ORDER BY id DESC LIMIT 5");
						}
		
						//check if any post exist
						$getposts_check = mysqli_num_rows($getposts);
						if ($getposts_check > 0 && $getposts_check < 5) {
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
									header("Location: home.php");
		
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
									header("Location: home.php");
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
		
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								if (empty($profilepic_info)) {
									$profilepic_info = 'default/default_pic.jpg';
								}
		
								echo "<div class='s_Posts'>";
		
								echo "<form action='home.php' method='POST'>
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
						elseif ($getposts_check == 5) {
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
									header("Location: home.php");
		
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
									header("Location: home.php");
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
								
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								if (empty($profilepic_info)) {
									$profilepic_info = 'default/default_pic.jpg';
								}
		
								echo "<div class='s_Posts'>";
		
								echo "<form action='home.php' method='POST'>
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
		
							echo "<form action='home.php' method='POST'>
									<input type='submit' name='more' value='Show More' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
								</form>
							";
						}
						elseif ($getposts_check > 5) {
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
									header("Location: home.php");
		
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
									header("Location: home.php");
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
								
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];
		
								$get_user_info = mysqli_query($conn, "SELECT * FROM users WHERE username = '$added_by'");
								$get_info = mysqli_fetch_assoc($get_user_info);
								$profilepic_info = $get_info['profile_pic'];

								if (empty($profilepic_info)) {
									$profilepic_info = 'default/default_pic.jpg';
								}
		
								echo "<div class='s_Posts'>";
		
								echo "<form action='home.php' method='POST'>
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
		
							echo "<form action='home.php' method='POST'>
									<input type='submit' name='less' value='Show Less' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
								</form>
							";
						}
						else
						{
		
							echo "<div class='s_Posts'>";
		
							echo "<em>$friendUsername hasn't posted anything yet!</em><br>";
		
							echo "</div>";
						}

					}
				}
				else {
					echo "<div class='s_Posts'>";
		
					echo "<em>You don't yet have a friend!</em><br>";

					echo "</div>";
				}
				
			?>
		</form>
	</div>
</div>
<script src="js/main.js" type="text/javascript"></script>