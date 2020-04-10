<?php 
    include("inc/header.inc.php");

    if (isset($_GET['uid'])) {
        $picture = mysqli_real_escape_string($conn,$_GET['uid']);
        if (ctype_alnum($picture)) {
                //check album exists
            $check = mysqli_query($conn,"SELECT * FROM photos WHERE pid ='$picture'");
            if (mysqli_num_rows($check)>0) {
                $get = mysqli_fetch_assoc($check);
                $username = $get['username'];
                $title = $get['album_title'];

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
                                $date_posted = date("Y-m-d H:i:s");
                                $profilepic_query = mysqli_query($conn, "INSERT INTO photos (`id`, `pid`, `username`, `date_posted`, `album_title`, `image_url`, `removed`) VALUES ('','$picture','$user','$date_posted','$title','http://localhost/G-Media/userdata/user_photos/$rand_dir_name/$profilepic_name','no')");
                                header("location: view_photo.php?uid=$picture");
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
            exit("<h2>Sorry, no such album exist!<h2>");
            }
        }
    }
?>

<a href="<?php echo "view_albums.php?u=".$username; ?>" class="go_back">Go back &larr;</a>
<div class="div_wrapper">

    <?php 
        if ($user == $username) {
            echo "<form method='POST' enctype='multipart/form-data'>
                    <p><strong>UPLOAD PHOTO:</strong></p>
                    <input type='file' name='profilepic'><br><br>
                    <input type='submit' name='uploadpic' value='Upload Photo'>
                </form>";
        }
    ?>

    <h2><?php echo $title; ?> Album</h2>
    <div class="div_albums">

        <?php 

            $get_photos = mysqli_query($conn,"SELECT * FROM photos WHERE pid = '$picture' AND removed = 'no'");
            $numrows = mysqli_num_rows($get_photos);
            while($row = mysqli_fetch_assoc($get_photos)) {
                $id = $row['id'];
                $username = $row['username'];
                $date = $row['date_posted'];
                $image_url = $row['image_url'];

                if (isset($_POST['dlt_photo_'.$id])) {
                    $dlt_photo = mysqli_query($conn, "UPDATE photos SET removed = 'yes' WHERE id = '$id'");

                    header("Location: view_photo.php?uid=$picture");
                }

                if ($user == $username) {
                    echo "<div class='albums'>
                            <a href='$image_url'>
                                <img src='$image_url' alt='' height='200' width='200'><br><br>
                            </a>
                            <center>
                                <form action='view_photo.php?uid=$picture' method='POST'>
                                    <input type='submit' name='dlt_photo_$id' value='Remove Photo'>
                                </form>
                            </center>
                        </div>";
                }
                else {
                    echo "<div class='albums'>
                            <a href='$image_url'>
                                <img src='$image_url' alt='' height='200' width='200'><br><br>
                            </a>
                        </div>";
                }
            }
        ?>

    </div>
</div>
<script src="js/main.js" type="text/javascript"></script>