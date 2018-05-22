<?php
include('./classes/db.php');
include('./classes/log_in.php');
include('./classes/post.php');
include('./classes/comment.php');

$showTimeline = false;

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
//search function
if (isset($_POST['searchBox'])){
    $tosearch = explode(" ", $_POST['searchBox']);
    if (count($tosearch) == 1) {
        $tosearch = str_split($tosearch[0], 2);
    }
    $whereclause = "";
    $paramsarray = array(':username'=>'%'.$_POST['searchBox'].'%');
    for ($i = 0; $i < count($tosearch); $i++) {
        $whereclause .= " OR username LIKE :u$i ";
        $paramsarray[":u$i"] = $tosearch[$i];
    }
    $users = DB::query('SELECT users.username FROM users WHERE users.username LIKE :username '.$whereclause.'', $paramsarray);
    echo "<pre>";
    print_r($users);
    echo "</pre>";
    $whereclause = "";
    $paramsarray = array(':body'=>'%'.$_POST['searchBox'].'%');
    for ($i = 0; $i < count($tosearch); $i++) {
        if ( $i % 2){
            $whereclause .= " OR body LIKE :p$i ";
            $paramsarray[":p$i"] = $tosearch[$i];
        }
    }
    $posts = DB::query('SELECT posts.body FROM posts WHERE posts.body LIKE :body '.$whereclause.'', $paramsarray);
    echo "<pre>";
    print_r($posts);
    echo "</pre>";
}
?>
<form action="index.php" method="post">
    <input type="text" name="searchBox" placeholder="Type username here" />
    <input type="submit" name="search" value="Search">
</form>

<?php
//To print the posts from database ,ang ma print na post dgd kung kisay ang finafollow lang kang nka login na user
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
        echo "<input type='submit' name='unlike' value='Unlike'>"   ;
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