<?php
include('./classes/db.php');
include('./classes/log_in.php');
$showTimeline = false;
if (login::isLoggedIn()){
    $showTimeline = true;
} else {
    echo "Not logged in!";
}

$followingposts =DB::query('SELECT posts.body, posts.likes, users.`username` FROM users,posts,followers  
                                WHERE posts.user_id = followers.user_id 
                                AND users.id = posts.user_id 
                                AND follower_id = 3 ORDER BY posts.likes DESC ;');
foreach ($followingposts as $posts){
    echo $posts['body']." -".$posts['username']." ".$posts['likes']."likes"."<hr />";
}