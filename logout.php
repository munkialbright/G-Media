<?php 

	include ("inc/connect.inc.php");

	session_start();
	if (!isset($_SESSION['user_login'])) {
		$user = "";
	}
	else {
		$user = $_SESSION["user_login"];

		$time = date("Y-m-d H:i:s");
		$status_query = mysqli_query($conn, "UPDATE `online_status` SET `status` = '1' WHERE username = '$user'");
		$status_query1 = mysqli_query($conn, "UPDATE `online_status` SET `time` = '$time' WHERE username = '$user'");
	}
	session_destroy();
	header("location: index.php");
 ?>