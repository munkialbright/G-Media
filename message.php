<?php include("inc/header.inc.php"); ?>

<div class="div_wrapper">
	<?php 

		$selectFriendsQuery = mysqli_query($conn, "SELECT friend_array FROM users WHERE username = '$user'");
		$friendRow = mysqli_fetch_assoc($selectFriendsQuery);
		$friendArray = $friendRow['friend_array'];
		if ($friendArray != "") {
			$friendArray = explode(",", $friendArray);
			$countFriends = count($friendArray);
			$friendArray12 = array_slice($friendArray, 0);

	?>

	<style>
		.msg_div:hover {
			background-color: rgb(224, 232, 240);
		}
	</style>

	<h2>Unread Messages</h2>

	<?php
		
			$i = 0;

			$grab_messages = mysqli_query($conn, "SELECT * FROM pvt_messages WHERE user_to = '$user' AND opened = 'no'");
			$numrows_unread = mysqli_num_rows($grab_messages);
			if ($numrows_unread != 0) {

				foreach ($friendArray12 as $key => $value) {
					$i++;
					$getFriendQuery = mysqli_query($conn, "SELECT * FROM users WHERE username='$value' LIMIT 1");
					$getFriendRow = mysqli_fetch_assoc($getFriendQuery);
					$friendUsername = $getFriendRow['username'];
					$friendProfilePic = $getFriendRow['profile_pic'];

					if (empty($friendProfilePic)) {
						$friendProfilePic = 'default/default_pic.jpg';
					}

					//Grab the messages for the logged in user
					$grab_messages = mysqli_query($conn, "SELECT * FROM pvt_messages WHERE user_to = '$user' AND user_from = '$friendUsername' AND opened = 'no' AND user_from_deleted = 'no' AND user_to_deleted = 'no' ORDER BY id DESC LIMIT 1");
					$get_msg = mysqli_fetch_assoc($grab_messages);
					$id = $get_msg['id'];
					$user_from = $get_msg['user_from'];
					$user_to = $get_msg['user_to'];
					$msg_body = $get_msg['msg_body'];
					$date = $get_msg['date'];
					$opened = $get_msg['opened'];
					$user_from_deleted = $get_msg['user_from_deleted'];
					$user_to_deleted = $get_msg['user_to_deleted'];

					$get_unread_query = mysqli_query($conn, "SELECT opened FROM pvt_messages WHERE user_to = '$user' AND user_from = '$friendUsername' AND opened = 'no' AND user_from_deleted = 'no' AND user_to_deleted = 'no' ORDER BY id DESC");
					$unread_numrows = mysqli_num_rows($get_unread_query);

					$online_query = mysqli_query($conn,"SELECT * FROM online_status WHERE username='$friendUsername'");
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

					if ($user == $user_to) {

						if (strlen($msg_body) > 50) {
							$msg_body2 = $msg_body;
							$msg_body = substr($msg_body, 0, 50)."...";

							echo "<a href='my_messages.php?u=$friendUsername' style='text-decoration: none;'><div class='msg_div'><img src='userdata/profile_pics/$friendProfilePic' alt=\"$friendUsername's Profile\" title=\"$friendUsername's Profile\" height='40' width='30' style='border-radius: 50%;'> $friendUsername ($unread_numrows): $msg_body<span class='go_r'>$online</span><br></div></a><hr>";
						}
						else
						{
							$msg_body = $msg_body;

							echo "<a href='my_messages.php?u=$friendUsername' style='text-decoration: none;'><div class='msg_div'><img src='userdata/profile_pics/$friendProfilePic' alt=\"$friendUsername's Profile\" title=\"$friendUsername's Profile\" height='40' width='30' style='border-radius: 50%;'> $friendUsername ($unread_numrows): $msg_body<span class='go_r'>$online</span><br></div></a><hr>";
						}

					}
				}
			}
			else
			{
				echo "You don't yet have any unread messages...";
			}
	 ?>

	 <h2>Friends</h2>

	<?php
		
			$i = 0;

			foreach ($friendArray12 as $key => $value) {
				$i++;
				$getFriendQuery = mysqli_query($conn, "SELECT * FROM users WHERE username='$value' LIMIT 1");
				$getFriendRow = mysqli_fetch_assoc($getFriendQuery);
				$friendUsername = $getFriendRow['username'];
				$friendProfilePic = $getFriendRow['profile_pic'];

				if (empty($friendProfilePic)) {
					$friendProfilePic = 'default/default_pic.jpg';
				}

				$online_query = mysqli_query($conn,"SELECT * FROM online_status WHERE username='$friendUsername'");
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
				
				echo "<a href='my_messages.php?u=$friendUsername' style='text-decoration: none;'><div class='msg_div'><img src='userdata/profile_pics/$friendProfilePic' alt=\"$friendUsername's Profile\" title=\"$friendUsername's Profile\" height='40' width='30' style='border-radius: 50%;'> $friendUsername<span class='go_r'>$online</span><br></div></a><hr>";
			}
		}
		else {
			echo "You have no friends yet!";
		}
	 ?>
</div>
<script src="js/main.js" type="text/javascript"></script>