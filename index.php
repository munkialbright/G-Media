<?php include("inc/header.inc.php");
	$reg = @$_POST['reg'];

	//declaring variable to prevent errors
	$fn = ""; //First Name
	$ln = ""; //Last Name
	$sex = ""; //Gender
	$age = ""; //Age
	$nid = ""; //ID card No
	$un = ""; //Username
	$em = ""; //Email
	$pswd = ""; //Password
	$pswd2 = ""; //Password2
	$d = ""; //Sign up Date
	$u_check = ""; //check if username exists

	//registration form
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$fn = test_input(@$_POST['fname']);
		$ln = test_input(@$_POST['lname']);
		if (!empty(@$_POST['gender'])) {
		    $sex = test_input(@$_POST['gender']);
		} else {
			
		}
		$age = test_input(@$_POST['age']);
		$nid = test_input(@$_POST['nic']);
		$un = test_input(@$_POST['username']);
		$em = test_input(@$_POST['email']);
		$pswd = test_input(@$_POST['password']);
		$pswd2 = test_input(@$_POST['password2']);
		$d = date("Y-m-d"); //Year-Month-Day
	}

	function test_input($data) {
		$data = strip_tags($data);
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	if ($reg) {
		if ($em) {
			// check if e-mail address is well-formed
		    if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
		    	// Check if user already exists
				$u_check = mysqli_query($conn,"SELECT username FROM users WHERE username='$un'");
				// Count the amount of rows where username = $un
				$check = mysqli_num_rows($u_check);
				//Check whether Email already exists in the database
				$e_check = mysqli_query($conn,"SELECT email FROM users WHERE email='$em'");
				//Count the number of rows returned
				$email_check = mysqli_num_rows($e_check);
				if ($check == 0) {
					if ($email_check == 0) {
						//check all of the fields have been filed in
						if ($fn&&$ln&&$sex&&$age&&$nid&&$un&&$em&&$pswd&&$pswd2) {
							if ($age>=16) {
								if (strlen($nid)>=9||strlen($nid)<=15) {
									// Check if user already exists
									$id_check = mysqli_query($conn,"SELECT id_card_num FROM users WHERE id_card_num='$nid'");
									// Count the amount of rows where username = $un
									$nid_check = mysqli_num_rows($id_check);
									if ($nid_check == 0) {
										if ($pswd==$pswd2) {
										// check the maximum length of username/first name/last name does not exceed 25 characters
											if (strlen($un)>25||strlen($fn)>25||strlen($ln)>25) {
												echo "The maximum limit for username/first name/last name is 25 characters!";
											}
											else
											{
												// check the maximum length of password does not exceed 25 characters and is not less than 5 characters
												if (strlen($pswd)>30||strlen($pswd)<5) {
													echo "Your password must be between 5 and 30 characters long!";
												}
												else
												{
													//encrypt password and password 2 using md5 before sending to database
													$pswd = md5($pswd);
													$pswd2 = md5($pswd2);
													$sql_cmd = "INSERT INTO users (`id`, `username`, `first_name`, `last_name`, `email`, `password`, `gender`, `age`, `id_card_num`, `sign_up_date`, `profile_pic`, `activated`) VALUES ('','$un','$fn','$ln','$em','$pswd','$sex','$age','$nid','$d','default/default_pic.jpg','0')"; 
													$query = mysqli_query($conn,$sql_cmd);
													$time = date("Y-m-d H:i:s");
													$status_query = mysqli_query($conn, "INSERT INTO online_status (`username`, `status`, `time`) VALUES ('$un','1','$time')");
													exit("<center><h2>Welcome to G-Media</h2><p><strong>Your account has been successfully created. <a href='index.php' style='text-decoration: none;'>Login</a> to your account to get started!</strong></p></center>");
												}
											}
										}
										else
										{
											echo "Your passwords don't match!";
										}
									}
									else
									{
										echo "Sorry, but it looks someone has already used that National ID card number";
									}
								}
								else
								{
									echo "Sorry, but it but your National ID card number is invalide";
								}
							}
							else
							{
								echo "Sorry, you are under aged to register (<16yrs)";
							}
						}
						else
						{
							echo "Please fill in your gender!";
						}
					}
					else
					{
					 	echo "Sorry, but it looks like someone has already used that email!";
					}
				}
				else
				{
					echo "Username already taken ...";
				}
		    }
			else
			{
				echo "Invalid email format!";
			}
		}
		else {
			echo "You haven't entered your email!";
		}
	}

	//user login code
	if (isset($_POST["user_login"]) && isset($_POST["password_login"])) {
		$user_login = preg_replace('#[^A-Za-z0-9]#i', '', $_POST["user_login"]); // filter everything but numbers and letters
	    $password_login = preg_replace('#[^A-Za-z0-9]#i', '', $_POST["password_login"]); // filter everything but numbers and letters
	    $password_login_md5 = md5($password_login);
	    $sql = mysqli_query($conn,"SELECT id FROM users WHERE username='$user_login' AND password='$password_login_md5' LIMIT 1"); // query the person
		//Check for their existance
		$userCount = mysqli_num_rows($sql); //Count the number of rows returned
		if ($userCount == 1) {
			while($row = mysqli_fetch_array($sql)){ 
	             $id = $row["id"];
			}
			$_SESSION["user_login"] = $user_login;
			$time = date("Y-m-d H:i:s");
			$status_query = mysqli_query($conn, "UPDATE `online_status` SET `status` = '0' WHERE username = '$user_login'");
			$status_query1 = mysqli_query($conn, "UPDATE `online_status` SET `time` = '$time' WHERE username = '$user_login'");
			header("location: home.php");
	        exit();
			}
		else {
			echo "The information entered is incorrect. Please try again";
		}
	}
 ?>
 
		<div class="sign_div">
			<table class="table_1">
				<tr>
					<td valign="top" class="sign_td">
						<h2>Already a Member? Sign in below!</h2>
						<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="my_form1">
							<input type="text" name="user_login" size="25" placeholder="Username" class="my_subform1" required style="width: 90%;"><br><br>
							<input type="password" name="password_login" size="25" placeholder="Password" class="my_subform1" id="myInput" required style="width: 90%;"><br>
							<input type="checkbox" onclick="myFunction(myInput)">Show Password<br><br>
							<input type="submit" name="login" value="Sign In!"><br><br>
							<a href="reset.php">Forgotten password or username?</a>

						</form>
					</td>
					<td valign="top" class="sign_td">
						<h2>Sign Up Below!</h2>
						<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="my_form">
							<input type="text" name="fname" size="25" placeholder="First Name" class="my_subform" required><br><br>
							<input type="text" name="lname" size="25" placeholder="Last Name"  class="my_subform" required><br><br>
							<input type="radio" name="gender" value="Male">Male
							<input type="radio" name="gender" value="Female">Female
							<input type="radio" name="gender" value="Other">Other
							<br><br>
							<input type="number" name="age" size="25" placeholder="Age"  class="my_subform" required><br><br>
							<input type="text" name="nic" size="25" placeholder="National ID card number"  class="my_subform" required><br><br>
							<input type="text" name="username" size="25" placeholder="Username"  class="my_subform" required><br><br>
							<input type="email" name="email" size="25" placeholder="Email"  class="my_subform" required><br><br>
							<input type="password" name="password" size="25" placeholder="Password"  class="my_subform" id="myInput1" required><br>
							<input type="checkbox" onclick="myFunction(myInput1)">Show Password<br><br>
							<input type="password" name="password2" size="25" placeholder="Renter password"  class="my_subform" id="myInput2" required><br>
							<input type="checkbox" onclick="myFunction(myInput2)">Show Password<br><br>
							<input type="submit" name="reg" value="Sign Up!">
						</form>
					</td>
				</tr>
			</table>
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
<?php include("inc/footer.inc.php"); ?>		