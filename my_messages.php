<?php include("inc/header.inc.php"); ?>
<link rel="stylesheet" type="text/css" href="css/my_messages.css">

<div class="div_wrapper" id="my_msg_refreshing">

		<?php 

			if (isset($_GET['u'])) {
				$username = mysqli_real_escape_string($conn,$_GET['u']);
				if (ctype_alnum($username)) {
					//check user exists
					$check = mysqli_query($conn,"SELECT username FROM users WHERE username='$username'");
					if (mysqli_num_rows($check)===1) {
						$get = mysqli_fetch_assoc($check);
						$username = $get['username'];

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

						echo "<h2 style='padding-bottom: 0px;'>".$username.": ".$online."</h2>";
		?>

	<h2>Unread Messages</h2>

	<div class="container">

		<?php

						if (isset($_POST['submit'])) {
							$msg_body = strip_tags(@$_POST['msg_body']);
							$date = date("Y-m-d H:i:s");
							$opened = "no";

							$send_msg = mysqli_query($conn, "INSERT INTO `pvt_messages` (`id`, `user_from`, `user_to`, `msg_body`, `date`, `opened`) VALUES ('','$user','$username','$msg_body','$date','$opened')");

							header("Location: my_messages.php?u=$username");
						}

						//Grab the unread messages for the logged in user
						$grab_messages = mysqli_query($conn, "SELECT * FROM pvt_messages WHERE ((user_to = '$user' AND user_from = '$username') OR (user_to = '$username' AND user_from = '$user')) AND opened = 'no' AND user_from_deleted = 'no' AND user_to_deleted = 'no' ORDER BY id ASC");
						$numrows_unread = mysqli_num_rows($grab_messages);
						if ($numrows_unread != 0) {
							while ($get_msg = mysqli_fetch_assoc($grab_messages)) {
								$id = $get_msg['id'];
								$user_from = $get_msg['user_from'];
								$user_to = $get_msg['user_to'];
								$msg_body = $get_msg['msg_body'];
								$date = $get_msg['date'];
								$opened = $get_msg['opened'];
								$user_from_deleted = $get_msg['user_from_deleted'];
								$user_to_deleted = $get_msg['user_to_deleted'];

								if (@$_POST['delete_'.$id.'']) {

									if ($opened == 'no') {
										$delete_msg_user_from = mysqli_query($conn, "UPDATE pvt_messages SET user_from_deleted = 'yes' WHERE id = '$id' AND opened = 'no'");
										$delete_msg_user_to = mysqli_query($conn, "UPDATE pvt_messages SET user_to_deleted = 'yes' WHERE id = '$id' AND opened = 'no'");
										header("Location: my_messages.php?u=$username");
									}
									else {
										echo "The message has already been read!";
									}
								}
		?>

		<script type="text/javascript">
			function toggle<?php echo $id; ?>() {
				var elt = document.getElementById("toggleText<?php echo $id; ?>");
				var text = document.getElementById("displayText<?php echo $id; ?>");

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

							if ($user_from == $user) {
								if (strlen($msg_body) > 50) {
									$msg_body2 = $msg_body;
									$msg_body = substr($msg_body, 0, 50)."...";

									echo "
										<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='me_msg'>
												<p class='go_r' style='margin-right: 10px'><strong><a href='$user_from'>me</a></strong>: <span class='msg'><a id='displayText$id' href='javascript:toggle$id();'>".$msg_body."</a></span></p>
												<input type='submit' name='delete_$id' value='X' title='Delete for everyone' class='go_l' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
												<p class='go_l' style='margin-left: 5px'><strong> $date </strong></p>
												<br><br><br>
												<div id='toggleText$id' style='display: none; margin-left: 10px'>
													$msg_body2
												</div>
										</form>
									";
								}
								else
								{
									$msg_body = $msg_body;

									echo "
										<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='me_msg'>
												<p class='go_r' style='margin-right: 10px'><strong><a href='$user_from'>me</a></strong>: <span class='msg'>$msg_body</span></p>
												<input type='submit' name='delete_$id' value='X' title='Delete for everyone' class='go_l' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
												<p class='go_l' style='margin-left: 5px'><strong> $date </strong></p><br><br><br>
										</form>
									";
								}
							}
							else{
								if (strlen($msg_body) > 50) {
									$msg_body2 = $msg_body;
									$msg_body = substr($msg_body, 0, 50)."...";

									echo "
										<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='you_msg' style='padding-top: 3px;'>
											<p style='margin-left: 10px;'><strong><a href='$user_from'>$user_from</a></strong>: <span class='msg'><a id='displayText$id' href='javascript:toggle$id();'>".$msg_body."</a></span><span class='go_r' style='margin-right: 10px;'><strong> $date </strong></span></p>
											<div id='toggleText$id' style='display: none; margin-left: 10px;'><br><br><br>
												$msg_body2
											</div>
										</form>
									";
								}
								else
								{
									$msg_body = $msg_body;

									echo "
										<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='you_msg' style='padding-top: 3px;'>
											<p style='margin-left: 10px;'><strong><a href='$user_from'>$user_from</a></strong>: <span class='msg'>$msg_body</span><span class='go_r' style='margin-right: 10px;'><strong> $date </strong></span></p>
										</form>
									";
								}

								//Set unread messages to read
								$setopened_query = mysqli_query($conn, "UPDATE pvt_messages SET opened = 'yes' WHERE id = '$id'");

							}

						}

					}
					else
					{
						echo "<p class='form_cnt'>You don't have any unread messages from ".$username." yet!</p>";
					}
			?>
	</div>
	<br>

	<h2>Read Messages</h2>

	<div class="container">
		
		<?php 

				//Grab the read messages for the logged in user
				$grab_messages = mysqli_query($conn, "SELECT * FROM pvt_messages WHERE (user_to = '$user' OR user_from = '$user') AND (user_to = '$username' OR user_from = '$username') AND opened = 'yes' AND (user_from_deleted = 'no' OR user_from_deleted = 'yes') AND (user_to_deleted = 'no' OR user_to_deleted = 'yes') ORDER BY id DESC");
				$numrows_read = mysqli_num_rows($grab_messages);
				if ($numrows_read != 0) {
					while ($get_msg = mysqli_fetch_assoc($grab_messages)) {
						$id = $get_msg['id'];
						$user_from = $get_msg['user_from'];
						$user_to = $get_msg['user_to'];
						$msg_body = $get_msg['msg_body'];
						$date = $get_msg['date'];
						$opened = $get_msg['opened'];
						$user_from_deleted = $get_msg['user_from_deleted'];
						$user_to_deleted = $get_msg['user_to_deleted'];

						if (@$_POST['user_from_delete_'.$id.'']) {
							$delete_msg_query = mysqli_query($conn, "UPDATE pvt_messages SET user_from_deleted = 'yes' WHERE id = '$id'");
							header("Location: my_messages.php?u=$username");
						}

						if (@$_POST['user_to_delete_'.$id.'']) {
							$delete_msg_query = mysqli_query($conn, "UPDATE pvt_messages SET user_to_deleted = 'yes' WHERE id = '$id'");
							header("Location: my_messages.php?u=$username");
						}
	 ?>

	<script type="text/javascript">
		function toggle<?php echo $id; ?>() {
			var elt = document.getElementById("toggleText<?php echo $id; ?>");
			var text = document.getElementById("displayText<?php echo $id; ?>");

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

								if ($user_from == $user) {

									if ($user_from_deleted == 'no') {
										if (strlen($msg_body) > 50) {
											$msg_body2 = $msg_body;
											$msg_body = substr($msg_body, 0, 50)."...";
	
											echo "
												<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='me_msg' style='padding-bottom: 5px;'>
														<p class='go_r' style='margin-right: 10px'><strong><a href='$user_from'>me</a></strong>: <span class='msg'><a id='displayText$id' href='javascript:toggle$id();'>$msg_body</a></span></p>
														<input type='submit' name='user_from_delete_$id' value='X' title='Delete Message' class='go_l' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
														<p class='go_l' style='margin-left: 5px;'><strong> $date </strong></p>
														<br><br><br>
														<div id='toggleText$id' style='display: none; margin-left: 10px'>
															$msg_body2
														</div>
												</form>
											";
										}
										else
										{
											$msg_body = $msg_body;
	
											echo "
												<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='me_msg' style='padding-bottom: 5px;'>
														<p class='go_r' style='margin-right: 10px'><strong><a href='$user_from'>me</a></strong>: <span class='msg'>$msg_body</span></p>
														<input type='submit' name='user_from_delete_$id' value='X' title='Delete Message' class='go_l' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
														<p class='go_l' style='margin-left: 5px;'><strong> $date </strong></p>
														<br><br><br>
												</form>
											";
										}
									}

								}
								else{

									if ($user_to_deleted == 'no') {
										if (strlen($msg_body) > 50) {
											$msg_body2 = $msg_body;
											$msg_body = substr($msg_body, 0, 50)."...";
	
											echo "
												<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='you_msg' style='padding-top: 3px; padding-bottom: 5px;'>
													<p style='margin-left: 10px;' class='go_l'><strong><a href='$user_from'>$user_from</a></strong>: <span class='msg'><a id='displayText$id' href='javascript:toggle$id();'>$msg_body</a></span></p>
													<input type='submit' name='user_from_delete_$id' value='X' title='Delete Message' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
													<p class='go_r' style='margin-right: 5px;'><strong> $date </strong></p>
													<div id='toggleText$id' style='display: none; margin-left: 10px;'><br><br><br>
														$msg_body2
													</div>
												</form>
											";
										}
										else
										{
											$msg_body = $msg_body;
	
											echo "
												<form method='POST' action='my_messages.php?u=$username' name='$msg_body' class='you_msg' style='padding-top: 3px; padding-bottom: 5px;'>
													<p style='margin-left: 10px;' class='go_l'><strong><a href='$user_from'>$user_from</a></strong>: <span class='msg'>$msg_body</span></p>
													<input type='submit' name='user_from_delete_$id' value='X' title='Delete Message' class='go_r' style='background-color: rgb(179, 196, 209); border: 1px solid rgb(179, 196, 209);'>
													<p class='go_r' style='margin-right: 5px;'><strong> $date </strong></p>
												</form>
											";
										}
									}

								}
							}
						}
						else
						{
							echo "<p class='form_cnt'>You haven't read any of ".$username."'s messages yet!</p>";
						}

						echo "
							<form action='my_messages.php?u=$username' method='POST' class='form_reply'>
								<textarea cols='110' rows='1' name='msg_body' placeholder='Enter the message you wish to send...' required></textarea>
								<input type='submit' name='submit' value='Send' class='go_r' style='margin-left: 10px'>
							</form>
						";

					}
				}
			}
		 ?>
	 </div>

 </div>
 <script src="js/main.js" type="text/javascript"></script>