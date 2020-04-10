<?php include("inc/header.inc.php"); ?>

<div class="div_wrapper">

	<?php 

		if ($user) {
			
		}
		else{
			die("<center><strong>You must be logged in to view this page!</strong> <a href='index.php'>Log In?</a></center>");
		}

		//First Name, Last Name and About You
		$get_info = mysqli_query($conn,"SELECT first_name, last_name, email, bio FROM users WHERE username = '$user'");
		$get_row = mysqli_fetch_assoc($get_info);
		$db_first_name = $get_row['first_name'];
		$db_last_name = $get_row['last_name'];
		$db_email = $get_row['email'];
		$db_bio = $get_row['bio'];

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$update_p = @$_POST['update_p'];
			if ($update_p) {
				$oldpassword = test_input($_POST['oldpassword']);
				$newpassword = test_input($_POST['newpassword']);
				$newpassword2 = test_input($_POST['newpassword2']);
				//encrypt the old password with md5 before check it
				$oldpassword_md5 = md5($oldpassword);

				$p_check = mysqli_query($conn,"SELECT password FROM users WHERE username='$user'");
				while ($row = mysqli_fetch_assoc($p_check)) {
					$db_pwd = $row['password'];

					//check if old password equals $db_password
					if ($oldpassword_md5 == $db_pwd) {
						//continue changing the users password

						//check whether the 2 new passwords match
						if ($newpassword == $newpassword2) {
							if (!strlen($newpassword)<5 || !strlen($newpassword)>30) {
								$newpassword_md5 = md5($newpassword);
								$pwd_query = mysqli_query($conn,"UPDATE users SET password='$newpassword_md5' WHERE username='$user'");
								echo "Great! Your password has been updated!";
							}
							else
							{
								echo "Sorry, but your new password must be between 5 and 30 characters...";
							}
						}
						else
						{
							echo "The new passwords don't match!";
						}
					}
					else
					{
						echo "The old password entered is incorrect!";
					}
				}
			}

			$update = @$_POST['update'];
			if ($update) {
				//If the form is submitted...
				if (!empty($_POST["fname"]) || !empty($_POST["lname"]) || !empty($_POST["about_u"] || !empty($_POST["email"]))) {
					$first_name = test_input($_POST['fname']);
					$last_name = test_input($_POST['lname']);
					$email = test_input($_POST['email']);
					$bio = htmlspecialchars($_POST['about_u']);
					if (!strlen($first_name)>25 || !empty($first_name)) {
						//submit what the user types into the database
						$submit_query = mysqli_query($conn,"UPDATE users SET first_name ='$first_name' WHERE username='$user'");
					}

					if (!strlen($last_name)>25 || !empty($last_name)) {
						//submit what the user types into the database
						$submit_query = mysqli_query($conn,"UPDATE users SET last_name ='$last_name' WHERE username='$user'");
					}

					if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
						//submit what the user types into the database
						$submit_query = mysqli_query($conn,"UPDATE users SET email ='$email' WHERE username='$user'");
					}

					if (!empty($bio)) {
						//submit what the user types into the database
						$submit_query = mysqli_query($conn,"UPDATE users SET bio = '$bio' WHERE username='$user'");
					}
					header("location: $user");
				}
				else
				{
				    echo "You haven't submitted any update yet!";
				}
			}

			if(@$_POST['close_account']) {

				echo "<center style='margin-top: 25%'>
						<form action='account_settings.php' method='POST'>
						<strong>Are you sure you want to close your account?</strong><br>
						<input type='submit' name='no' value='No'>
						<input type='submit' name='yes' value='Yes'>
						</form>
					</center>";
				exit();
			}

			if (isset($_POST['no'])) {
				header("Location: account_settings.php");
			}
			if (isset($_POST['yes'])) {
				$close_status = mysqli_query($conn, "DELETE FROM online_status WHERE username = '$user'");
				$close = mysqli_query($conn, "DELETE FROM users WHERE username = '$user'");
				session_destroy();
				exit("<center><strong>Account has been successfully closed!</strong> <a href='index.php'>Sign Up?</a></center>");
			}
		}

		//check whether the user has uploaded a profile pic or not
		$check_pic = mysqli_query($conn, "SELECT profile_pic FROM users WHERE username = '$user'");
		$get_pic_row = mysqli_fetch_assoc($check_pic);
		$profilepic_db = $get_pic_row['profile_pic'];

		$profilepic = "userdata/profile_pics/".$profilepic_db;

		//profile image upload script
		if (isset($_FILES['profilepic'])) {
			if ((($_FILES["profilepic"]["type"] == "image/jpeg") || (@$_FILES["profilepic"]["type"] == "image/png") || (@$_FILES["profilepic"]["type"] == "image/gif")) && (@$_FILES["profilepic"]["size"] <= 1048576 /*1Mb*/)) 
			{
				$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
				$rand_dir_name = substr(str_shuffle($chars), 0, 15);
				mkdir("userdata/profile_pics/$rand_dir_name");

				if (file_exists("userdata/profile_pics/$rand_dir_name/".@$_FILES["profilepic"]["name"])) {
					echo @$_FILES["profilepic"]["name"]."Already exists";
				}
				else
				{
					move_uploaded_file(@$_FILES["profilepic"]["tmp_name"], "userdata/profile_pics/$rand_dir_name/".@$_FILES["profilepic"]["name"]);
					//echo "Uploaded and stored in: ".@$_FILES["profilepic"]["name"];
					$profilepic_name = @$_FILES["profilepic"]["name"];
					$profilepic_query = mysqli_query($conn, "UPDATE users SET profile_pic = '$rand_dir_name/$profilepic_name' WHERE username = '$user'");
					header("location: account_settings.php");

					$date = date("Y-m-d H:i:s");

					$profile_photo = mysqli_query($conn, "INSERT INTO photos (`id`, `pid`, `username`, `date_posted`, `album_title`, `image_url`, `removed`) VALUES ('','$user','$user','$date','$user','http://localhost/G-Media/userdata/profile_pics/$rand_dir_name/$profilepic_name','no')");

					$post_query = mysqli_query($conn, "INSERT INTO posts (`id`, `body`, `image`, `date_added`, `added_by`, `user_posted_to`, `deleted`) VALUES ('', 'Profile picture has been updated.', 'http://localhost/G-Media/userdata/profile_pics/$rand_dir_name/$profilepic_name', '$date', '$user', 'Friends', 'no')");
				}
			}
			else
			{
				echo "Sorry, you can only upload an image of file formate jpeg/png/gif and of size 1Mb maximum...";
			}
		}

		function test_input($data) {
			$data = strip_tags($data);
			$data = trim($data);
			$data = stripslashes($data);
			$data = htmlspecialchars($data);
			return $data;
		}
	 ?>

	<h2>Edit your Account Settings below</h2>
	<hr>
	<form method="POST" enctype="multipart/form-data">
		
		<p> UPLOAD YOUR PROFILE PHOTO</p><br>
		<img src="<?php echo $profilepic ?>" width="70" alt="<?php echo $user; ?>" title="<?php echo $user; ?>`s profile picture"><br><br>
		<input type="file" name="profilepic"><br><br>
		<input type="submit" name="uploadpic" value="Upload Image">
		
	</form>
	<hr>
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
	 	
	 	<p>CHANGE YOUR PASSWORD</p><br>
		<input type="password" name="oldpassword"  placeholder="Your Old Password" class="my_subform" id="myInput3" required><br>
		<input type="checkbox" onclick="myFunction(myInput3)">Show Password<br><br>
		<input type="password" name="newpassword" placeholder="Your New Password" class="my_subform" id="myInput4" required><br>
		<input type="checkbox" onclick="myFunction(myInput4)">Show Password<br><br>
		<input type="password" name="newpassword2" placeholder="Repeat Password" class="my_subform" id="myInput5" required><br>
		<input type="checkbox" onclick="myFunction(myInput5)">Show Password<br><br>
		<input type="submit" name="update_p" value="Update">
	</form>

	<hr>
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
		
		<p>UPDATE YOUR PROFILE INFORMATION</p><br>
		<input type="text" name="fname" placeholder="<?php echo $db_first_name; ?>" class="my_subform"><br><br>
		<input type="text" name="lname" placeholder="<?php echo $db_last_name; ?>" class="my_subform"><br><br>
		<input type="email" name="email" placeholder="<?php echo $db_email; ?>" class="my_subform"><br><br>
		<textarea placeholder="<?php if(!empty($db_bio)) { echo $db_bio; } else { echo("About You"); } ?>" name="about_u" cols="90" rows="4" style="width: 99%; height: auto;"></textarea><br><br>
		<input type="submit" name="update" value="Update">
		<input type="submit" name="close_account" value="Close Account" title="This will permenently close your account" class="go_r">
		
	</form>

</div>
<script src="js/main.js" type="text/javascript"></script>
<script>
	//Show password
	function myFunction(x) {
		if (x.type === "password") {
			x.type = "text";
		} else {
			x.type = "password";
		}
	}
</script>