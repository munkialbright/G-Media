<?php include("inc/header.inc.php"); ?>
<link rel="stylesheet" type="text/css" href="css/home.css">

<div class="newsFeed">
	<h2>News</h2>
</div>
<div class="div_wrapper" id="newsResults">
    <?php

    $api_url = 'http://newsapi.org/v2/everything?sources=bbc-news&sortBy=latest&apiKey=daebf297e7cc4c899900e62700ebded1';

    // Read JSON file
    $json_data = file_get_contents($api_url);

    if ($json_data) {
        // Decode JSON data into PHP array
        $response_data = json_decode($json_data);

        // All user data exists in 'data' object
        $user_data = $response_data->articles;

        // Cut long data into small & select only first 10 records
        $user_data = array_slice($user_data, 0, 9);
        
        foreach ($user_data as $user) {
            echo '<div class="s_Posts">
            <img src="'.$user->urlToImage.'" class="responsive-img" alt="'.$user->title.'" height="100%" width="100%">
            <h3>Title: <a href="'.$user->url.'" target="blank" title="'.$user->title.'">'.$user->title.'</a></h3>
            <p><strong>News source</strong>: '.$user->source->name.' </p>
            <p><strong>Description</strong>: '.$user->description.'</p>
            <p><strong>Published</strong>: '.$user->publishedAt.' </p>
            <a href="'.$user->url.'" target="blank" class="btn">Read More</a>
        </div>';
        }
    }
    else {
        echo "<div class='errorMsg center'>An error occurred, couldn't connect to the news server.</div>";
    }

    ?>
</div>
<!-- <script src="js/news.js" type="text/javascript"></script> -->
<script src="js/main.js" type="text/javascript"></script>
