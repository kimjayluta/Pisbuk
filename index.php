<?php
include('./classes/db.php');
include('./classes/log_in.php');
include('./classes/post.php');
include('./classes/comment.php');

$showTimeline = false;
$userid = "";

if (login::isLoggedIn()){
    $userid = login::isLoggedIn();
    $showTimeline = true;

} else {
    die ("Not logged in!");
}

//Like function
if (isset($_GET['postid'])) {
    post::likePost($_GET['postid'],$userid);
}
//comment function
if (isset($_POST['comment'])) {
    comment::createComment($_POST['commentbody'], $_GET['postid'], $userid);
}
//To print the posts from database
$followingposts =DB::query('SELECT posts.body, posts.likes, posts.id, users.`username` FROM users,posts,followers  
                                WHERE posts.user_id = followers.user_id 
                                AND users.id = posts.user_id 
                                AND follower_id =:userid ORDER BY posts.likes DESC ;', array(':userid'=>$userid));
foreach($followingposts as $post) {
    echo $post['body']." ~ ".$post['username'];
    echo "<form action='index.php?postid=".$post['id']."' method='post'>";
    if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$post['id'], ':userid'=>$userid))) {
        echo "<input type='submit' name='like' value='Like'>";
    } else {
        echo "<input type='submit' name='unlike' value='Unlike'>";
    }
    echo"<span>".$post['likes']." likes</span>    </form>
         <form action='index.php?postid=".$post['id']."' method='post'>
         <textarea name='commentbody' cols='50' rows='3'></textarea><br /><p />
         <input type='submit' name='comment' value='Post comment'>
         </form>";
        comment::displayComment($post['id']);
        echo "
        <hr /></br />";
}