<?php include("inc/header.inc.php"); ?>
<style>
    .msg_div:hover {
        background-color: rgb(224, 232, 240);
    }
</style>
<div class="div_wrapper">
    <?php 
        if (isset($_GET['q'])) {
            $q = $_GET['q'];
            $search_query = mysqli_query($conn, "SELECT * FROM users WHERE username LIKE '%$q%' OR first_name LIKE '%$q%' OR last_name LIKE '%$q%'");
            $search_numrows = mysqli_num_rows($search_query);
            echo "<h2>$search_numrows results for '$q'</h2>";
            while ($search_row = mysqli_fetch_assoc($search_query)) {
                $search_username = $search_row['username'];
                $search_profile = $search_row['profile_pic'];

                $online_query = mysqli_query($conn,"SELECT * FROM online_status WHERE username='$search_username'");
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
                
                echo "<a href='$search_username' style='text-decoration: none;'><div class='msg_div'><img src='userdata/profile_pics/$search_profile' alt=\"$search_username's Profile\" title=\"$search_username's Profile\" height='40' width='30'> $search_username<span class='go_r'>$online</span><br></div></a><hr>";
            }
        }
    ?>
</div>
<script src="js/main.js" type="text/javascript"></script>