<?php 
    include("inc/header.inc.php");

    if (isset($_POST['feedback'])) {
        $email = test_input(@$_POST['email']);
        $subject = test_input(@$_POST['subject']);
        $body = test_input(@$_POST['body']);
        $date = date("Y-m-d H:i:s");

        // Validate email 
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $feedback_query = mysqli_query($conn, "INSERT INTO feedback (`id`, `username`, `email`, `subject`, `body`, `date`) VALUES ('', '$user', '$email', '$subject', '$body', '$date')");

            echo "Feedback sent!";
        }
        else {
            echo "Invalide email! Please retry with a valide email.";
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
 <div class="div_wrapper">
 <h2>Feedback</h2>

 <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="my_form">
    <input type="email" name="email" size="25" placeholder="Email"  class="my_subform" required><br><br>
    <input type="text" name="subject" placeholder="Subject"  class="my_subform" required style="width: 99%;" maxlength="50"><br><br>
    <textarea name="body" id="" cols="110" rows="5" placeholder="Body" required></textarea><br><br>
    <input type="submit" name="feedback" value="Send">
</form>

</div>
<script src="js/main.js" type="text/javascript"></script>