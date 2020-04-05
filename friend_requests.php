<?php include("inc/header.inc.php"); ?>

<div class="div_wrapper">

	<h2>Friend Requests</h2>

	<div class="container">

		<?php 
			
			//Find friend requests
			$friendRequests = mysqli_query($conn, "SELECT * FROM friend_requests WHERE user_to = '$user'");
			$numrows = mysqli_num_rows($friendRequests);
			if ($numrows == 0) {
				echo "<span class='form_cnt'>You have no friend request at the time!</span>";
			}
			else
			{
				while ($get_row = mysqli_fetch_assoc($friendRequests)) {
					$id = $get_row['id'];
					$user_to = $get_row['user_to'];
					$user_from = $get_row['user_from'];
					$date_sent = $get_row['date_sent'];

					$get_user_info = mysqli_query($conn, "SELECT profile_pic FROM users WHERE username = '$user_from'");
					$get_info = mysqli_fetch_assoc($get_user_info);
					$profilepic_info = $get_info['profile_pic'];

					if (empty($profilepic_info)) {
						$profilepic_info = 'default/default_pic.jpg';
					}

					echo '<span class="rqst"><a href="'.$user_from.'"><img src="userdata/profile_pics/'.$profilepic_info.'" alt=\"'.$user_from.'\" title=\"'.$user_from.'`s Account\" height="40" width="30" style="border-radius: 50%;">'.$user_from.'</a> wants to be friends</span><span class="go_r"><strong>'.$date_sent.'</strong></span>';
				

		 ?>
		<?php
			if (isset($_POST['acceptrequest'.$user_from])) {
				//select the friend_array row from the logged in user
				$get_friend_check = mysqli_query($conn, "SELECT friend_array FROM users WHERE username = '$user'");
				$get_friend_row = mysqli_fetch_assoc($get_friend_check);
				$friend_array = $get_friend_row['friend_array'];
				$friendArray_explode = explode(",", $friend_array);
				$friendArray_count = count($friendArray_explode);

				//select the friend_array row from the user who sent the friend request
				$get_friend_check_friend = mysqli_query($conn, "SELECT friend_array FROM users WHERE username = '$user_from'");
				$get_friend_row_friend = mysqli_fetch_assoc($get_friend_check_friend);
				$friend_array_friend = $get_friend_row_friend['friend_array'];
				$friendArray_explode_friend = explode(",", $friend_array_friend);
				$friendArray_count_friend = count($friendArray_explode_friend);

				//If the user has no friends, we just concat the friends username
				if ($friend_array == "") {
					$friendArray_count = NULL;
				}

				if ($friendArray_count == NULL) {
					$add_friend_query = mysqli_query($conn, "UPDATE users SET friend_array = CONCAT(friend_array,'$user_from') WHERE username = '$user'");
				}
				else
				{
					$add_friend_query = mysqli_query($conn, "UPDATE users SET friend_array = CONCAT(friend_array,',$user_from') WHERE username = '$user'");
				}

				if ($friend_array_friend == "") {
					$friendArray_count_friend = NULL;
				}

				if ($friendArray_count_friend == NULL) {
					$add_friend_query = mysqli_query($conn, "UPDATE users SET friend_array = CONCAT(friend_array,'$user_to') WHERE username = '$user_from'");
				}
				else
				{
					$add_friend_query = mysqli_query($conn, "UPDATE users SET friend_array = CONCAT(friend_array,',$user_to') WHERE username = '$user_from'");
				}

				$delete_request = mysqli_query($conn, "DELETE FROM friend_requests WHERE user_to = '$user_to' AND user_from = '$user_from'");

				header("Location: friend_requests.php");
			}

			if (isset($_POST['ignorerequest'.$user_from])) {
				$ignore_request = mysqli_query($conn, "DELETE FROM friend_requests WHERE user_to = '$user_to' AND user_from = '$user_from'");

				header("Location: friend_requests.php");
			}
		 ?>

		 <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="form_cnt">
		 	<input type="submit" name="acceptrequest<?php echo($user_from); ?>" value="Accept">
		 	<input type="submit" name="ignorerequest<?php echo($user_from); ?>" value="Ignore">
		 </form><hr>
		<?php 
				}
			}
		 ?>
	</div>
</div>
<script src="js/main.js" type="text/javascript"></script>