<?php 
    include("inc/header.inc.php");
    $reg = @$_POST['reset'];

	//declaring variable to prevent errors
	$fn = ""; //First Name
	$ln = ""; //Last Name
	$nid = ""; //ID card No
	$un = ""; //Username
	$em = ""; //Email
	$pswd = ""; //Password
	$pswd2 = ""; //Password2
	$u_check = ""; //check if username exists

	//registration form
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$fn = test_input(@$_POST['fname']);
		$ln = test_input(@$_POST['lname']);
		$nid = test_input(@$_POST['nic']);
		$em = test_input(@$_POST['email']);
		$pswd = test_input(@$_POST['password']);
		$pswd2 = test_input(@$_POST['password2']);
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
				//Check whether Email already exists in the database
				$e_check = mysqli_query($conn,"SELECT email FROM users WHERE email='$em'");
				//Count the number of rows returned
				$email_check = mysqli_num_rows($e_check);
                if ($email_check == 1) {
                    //check all of the fields have been filed in
                    if ($fn&&$ln&&$nid&&$em&&$pswd&&$pswd2) {
                        $info_query = mysqli_query($conn,"SELECT * FROM users WHERE email='$em' LIMIT 1");
                        $user_info = mysqli_fetch_assoc($info_query);
                        $username = $user_info['username'];
                        $fname = $user_info['first_name'];
                        $lname = $user_info['last_name'];
                        $email = $user_info['email'];
                        $user_id = $user_info['id_card_num'];

                        if (($fname == $fn) && ($lname == $ln)) {
                            if ($user_id == $nid) {
                                if ($pswd == $pswd2) {
                                    if (!strlen($pswd)<5 || !strlen($pswd)>30) {
                                        $newpassword_md5 = md5($pswd);
                                        $pwd_query = mysqli_query($conn,"UPDATE users SET password='$newpassword_md5' WHERE email = '$em'");
                                        exit('<center><p><strong>Great your password has been successfully changed! Your username is "<a href="" style="text-decoration: none; color: #800;">'.$username.'</a>". <a href="index.php" style="text-decoration: none;">Log In?</a></strong></p></center>');
                                    }
                                    else
                                    {
                                        echo "Sorry, but your new password must be between 5 and 30 characters...";
                                    }
                                }
                                else {
                                    echo "Your passwords don't match!";
                                }
                            }
                            else {
                                echo "The ID card number entered doesn't match with that of your account!";
                            }
                        }
                        else {
                            echo "Either your first name is wrong or your last name is wrong!";
                        }
                    }
                    else {
                        echo "Please fill in every box!";
                    }
                }
                else
                {
                    echo "The information entered, doesn't match that of any account!";
                }
            }
            else {
                echo "Invalide email formate!";
            }
        }
        else {
            echo "You haven't entered your email!";
        }
    }

?>
<a href="index.php" class="go_back">Go back &larr;</a>

<div class="div_wrapper">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="my_form">
    <h2>Fill in the following information as it is in your G-Media account</h2>
    <input type="text" name="fname" size="25" placeholder="First Name" class="my_subform" required><br><br>
    <input type="text" name="lname" size="25" placeholder="Last Name"  class="my_subform" required><br><br>
    <input type="text" name="nic" size="25" placeholder="National ID card number"  class="my_subform" required><br><br>
    <input type="email" name="email" size="25" placeholder="Email"  class="my_subform" required><br><br>
    <h2>Reset Password</h2>
    <input type="password" name="password" size="25" placeholder="New Password"  class="my_subform" id="myInput1" required><br>
    <input type="checkbox" onclick="myFunction(myInput1)">Show Password<br><br>
    <input type="password" name="password2" size="25" placeholder="Renter password"  class="my_subform" id="myInput2" required><br>
    <input type="checkbox" onclick="myFunction(myInput2)">Show Password<br><br>
    <input type="submit" name="reset" value="Submit">
</form>
</div>
<script src="js/main.js" type="text/javascript"></script>