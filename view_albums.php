<?php 
    include("inc/header.inc.php");

    if (isset($_GET['u'])) {
        $username = mysqli_real_escape_string($conn,$_GET['u']);
        if (ctype_alnum($username)) {
                //check user exists
            $check = mysqli_query($conn,"SELECT username, first_name FROM users WHERE username='$username'");
            if (mysqli_num_rows($check)===1) {
                $get = mysqli_fetch_assoc($check);
                $username = $get['username'];
                $firstname = $get['first_name'];

                $check_album = mysqli_query($conn,"SELECT * FROM albums WHERE created_by = '$username' AND album_title = '$username'");
                $check_row = mysqli_num_rows($check_album);

                if ($check_row == 0) {
                    $getProfile = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' LIMIT 1");
				    $getProfileRow = mysqli_fetch_assoc($getProfile);
                    $ProfilePic = $getProfileRow['profile_pic'];
                    $date = $getProfileRow['sign_up_date'];
                    
                    $profile_album = mysqli_query($conn, "INSERT INTO albums (`id`, `album_title`, `album_description`, `created_by`, `date_created`, `uid`, `default_image`, `removed`) VALUES ('','$username','Profile Post','$username','$date','$username','http://localhost/G-Media/userdata/profile_pics/$ProfilePic','no')");

                    $profile_photo = mysqli_query($conn, "INSERT INTO photos (`id`, `pid`, `username`, `date_posted`, `album_title`, `image_url`, `removed`) VALUES ('','$username','$username','$date','$username','http://localhost/G-Media/userdata/profile_pics/$ProfilePic','no')");
                }
                
                if (isset($_POST['uploadpic'])) {

                    if ((($_FILES["profilepic"]["type"] == "image/jpeg") || (@$_FILES["profilepic"]["type"] == "image/png") || (@$_FILES["profilepic"]["type"] == "image/gif")) && (@$_FILES["profilepic"]["size"] <= 1048576 /*1Mb*/)) 
                        {
                            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                            $rand_dir_name = substr(str_shuffle($chars), 1, 15);
                            mkdir("userdata/user_photos/$rand_dir_name");
            
                            if (file_exists("userdata/user_photos/$rand_dir_name/".@$_FILES["profilepic"]["name"])) {
                                echo @$_FILES["profilepic"]["name"]."Already exists";
                            }
                            else
                            {
                                move_uploaded_file(@$_FILES["profilepic"]["tmp_name"], "userdata/user_photos/$rand_dir_name/".@$_FILES["profilepic"]["name"]);
                                //echo "Uploaded and stored in: ".@$_FILES["profilepic"]["name"];
                                $profilepic_name = @$_FILES["profilepic"]["name"];

                                $uids = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                                $rand_uid_name = substr(str_shuffle($uids), 1, 15);

                                $title = @$_POST['title'];
                                $description = @$_POST['description'];
                                $date_created = date("Y-m-d H:i:s");

                                $album_query = mysqli_query($conn, "INSERT INTO albums (`id`, `album_title`, `album_description`, `created_by`, `date_created`, `uid`, `default_image`, `removed`) VALUES ('','$title','$description','$user','$date_created','$rand_uid_name','http://localhost/G-Media/userdata/user_photos/$rand_dir_name/$profilepic_name','no')");

                                $photo_query = mysqli_query($conn, "INSERT INTO photos (`id`, `pid`, `username`, `date_posted`, `album_title`, `image_url`, `removed`) VALUES ('','$rand_uid_name','$user','$date_created','$title','http://localhost/G-Media/userdata/user_photos/$rand_dir_name/$profilepic_name','no')");

                                header("location: view_photo.php?uid=$rand_uid_name");
                            }
                        }
                        else
                        {
                            echo "Sorry, you can only upload an image of file formate jpeg/png/gif and of size 1Mb maximum...";
                        }
                }
            }
            else
            {
            echo "<meta http-equiv=\"refresh\" content=\"0; url=http://localhost/G-Media/index.php\">";	
            exit();
            }
        }
    }
?>

<a href="<?php echo $username ?>" class="go_back">Go back &larr;</a>

<div class="div_wrapper">
    <?php 
        if ($user == $username) {
            echo "<form method='POST' enctype='multipart/form-data'>
                <h3 style='margin-left: 10px;'>CREATE NEW ALBUM</h3>
                    <input type='text' name='title' placeholder='Album Title' class='my_subform' required><br><br>
                    <textarea name='description' placeholder='Album Description' cols='55' rows='2' maxlength='50'></textarea>
                    <p><strong>UPLOAD PHOTO:</strong></p>
                    <input type='file' name='profilepic' required><br><br>
                    <input type='submit' name='uploadpic' value='Create Album'>
            </form>";
        }
    ?>

    <h2><?php echo $username; ?>'s Albums</h2>
    <div class="div_albums">

        <?php 
            $get_albums = mysqli_query($conn,"SELECT * FROM albums WHERE created_by = '$username' AND removed = 'no'");
            $numrows = mysqli_num_rows($get_albums);
            if ($numrows > 0) {

                while($row = mysqli_fetch_assoc($get_albums)) {
                    $id = $row['id'];
                    $title = $row['album_title'];
                    $description = $row['album_description'];
                    $created_by = $row['created_by'];
                    $date = $row['date_created'];
                    $uid = $row['uid'];
                    $image = $row['default_image'];

                    if (isset($_POST['dlt_album_'.$id])) {
                        $dlt_album = mysqli_query($conn, "UPDATE albums SET removed = 'yes' WHERE id = '$id'");
            
                        header("Location: view_albums.php?u=$username");
                    }

                    if ($user == $username) {
                        echo "<div class='albums'>
                                <a href='view_photo.php?uid=$uid' style='text-decoration: none;'>
                                    <img src='$image' height='170' width='170' alt='Album image' title='$description'><br><br>
                                    <span><strong>$title</strong></span>
                                </a>
                                <center>
                                    <form action='view_albums.php?u=$username' method='POST'>
                                        <input type='submit' name='dlt_album_$id' value='Remove Album'>
                                    </form>
                                </center>
                            </div>
                            ";
                    }
                    else {
                        echo "<div class='albums'>
                                <a href='view_photo.php?uid=$uid' style='text-decoration: none;'>
                                    <img src='$image' height='170' width='170' alt='Album image' title='$description'><br><br>
                                    <span><strong>$title</strong></span>
                                </a>
                            </div>";
                    }
                }

            }
            else
            {
                echo "<span style='margin-left: 10px;'>No Albums has been created yet!</span>";
            }

        ?>

    </div>
</div>
<script src="js/main.js" type="text/javascript"></script>