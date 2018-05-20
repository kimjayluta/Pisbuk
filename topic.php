<?php
include('./classes/db.php');
include('./classes/log_in.php');
include('./classes/post.php');
include('./classes/image.php');

if (isset($_GET['topic'])) {
    if (DB::query("SELECT topic FROM posts WHERE FIND_IN_SET(:topic, topic)", array(':topic'=>$_GET['topic']))) {

        $posts = DB::query("SELECT * FROM posts WHERE FIND_IN_SET(:topic, topic)", array(':topic'=>$_GET['topic']));
        foreach ($posts as $post){
            //echo "<pre>";print_r($post);echo "</pre>";
            echo $post['body']."<br />";
        }
    }
}