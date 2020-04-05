<link rel="stylesheet" href="css/comment_frame.css">

<?php
    include ("inc/connect.inc.php");

    if (isset($_GET['id'])) {
        $getid = mysqli_real_escape_string($conn,$_GET['id']);
        if (ctype_alnum($getid)) {
             //check post exists
            $check = mysqli_query($conn,"SELECT id FROM posts WHERE id='$getid'");
            if (mysqli_num_rows($check)===1) {
                $get = mysqli_fetch_assoc($check);
                $getid = $get['id'];

                session_start();
                if (!isset($_SESSION['user_login'])) {
                    $user = "";
                }
                else {
                    $user = $_SESSION["user_login"];
                }

                $cmt_body = "";

                if (isset($_POST['submit'])) {

                    //check user exists
                    $check = mysqli_query($conn,"SELECT username FROM users WHERE username='$user'");
                    if (mysqli_num_rows($check)===1) {

                        $getposts = mysqli_query($conn,"SELECT * FROM posts WHERE id='$getid' LIMIT 1");
                        $post = mysqli_fetch_assoc($getposts);

                        $get = mysqli_fetch_assoc($check);
                        $username = $get['username'];

                        $cmt_body = strip_tags(@$_POST['cmt_body']);
                        $date = date("Y-m-d H:i:s");
                        $removed = '0';
                        $posted_to = $post['added_by'];

                        $send_msg = mysqli_query($conn, "INSERT INTO `post_comments` (`id`, `post_body`, `posted_by`, `posted_to`, `post_removed`, `post_id`, `date`) VALUES ('','$cmt_body','$user','$posted_to','$removed','$getid','$date')");

                        header("Location: comment_frame.php?id=$getid");
                        echo "The comment has been deleted!";
                    }
                }

                //Get relevant comments
                $get_comments = mysqli_query($conn, "SELECT * FROM post_comments WHERE post_id='$getid' AND post_removed='0' ORDER BY id DESC");
                $count = mysqli_num_rows($get_comments);

                echo "<form action='comment_frame.php?id=$getid' method='POST' class='form_reply'>
                        <textarea cols='85' rows='1' name='cmt_body' placeholder='Enter your comment here...' required></textarea>
                        <input type='submit' name='submit' value='Send' class='go_r' style='margin-left: 10px'>
                    </form>";

                echo "<div class='iframe_container'>";

                if ($count != 0) {
                    while ($comment = mysqli_fetch_assoc($get_comments)) {
                        $cmt_id = $comment['id'];
                        $cmt_body = $comment['post_body'];	
                        $posted_to = $comment['posted_to'];
                        $posted_by = $comment['posted_by'];
                        $removed = $comment['post_removed'];
                        $date = $comment['date'];

                        if (@$_POST['delete_'.$cmt_id.'']) {
                            $delete_cmt_query = mysqli_query($conn, "UPDATE post_comments SET post_removed = '1' WHERE id = '$cmt_id'");
                            header("Location: comment_frame.php?id=$getid");
                        }

                        if ($user == $posted_by) {
                            echo "<form action='comment_frame.php?id=$getid' method='POST'>
                                <div>
                                    <a href='$posted_by' target='blank'>".$posted_by."</a>: ".$cmt_body."<span class='go_r'><strong>".$date."</strong></span>
                                </div>
                                <input type='submit' name='delete_$cmt_id' value=' ' title='Delete Comment' class='go_r' style='background-color: #DCE5EE; border: #DCE5EE; background-image:url(img/delete_comment.gif); background-repeat: no-repeat; border-radius: 0px;'>
                            </form><hr>";
                        }
                        else
                        {
                            echo "<a href='$posted_by' target='blank'>".$posted_by."</a>: ".$cmt_body."<span class='go_r'><strong>".$date."</strong></span><hr>";    
                        }

                    }
                }
                else {
                    echo "<center><br><br><br>No comments to display!</center><br>";
                }

                echo "</div>";
            }
        }
    }
?>