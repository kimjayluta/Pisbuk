<?php
include('./classes/db.php');
include('./classes/log_in.php');

$username = "";
$verified = false;
$isFollowing = false;
$userid = "";
$followerid = "";

if (login::isLoggedIn()){
    echo "Logged in! account id:".login::isLoggedIn();
} else {
    echo "Not logged in!";
    exit;
}

if (isset($_GET['username'])) {
    //checking if the username(username ng ifafollow) is in the database
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))) {
        //Username ng nsa get method
        $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['username'];
        //kinukua ang valye kang verified 0 means not verified and 1 means verified account
        $verified = DB::query('SELECT verified FROM users WHERE  username=:username', array(':username' => $_GET['username']))[0]['verified'];
        //kinukua ang id kang nsa link na username
        $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['id'];
        //id kang nka login
        $followerid = login::isLoggedIn();

        //follow function
        if (isset($_POST['follow'])) {
            if ($userid != $followerid) {
                //kapag dae pa finafollow
                if (!DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid,':followerid'=>$followerid))) {
                    if ($followerid == 3) {
                        DB::query('UPDATE users SET verified=1 WHERE id=:userid', array(':userid' => $userid));
                    }
                    DB::query('INSERT INTO followers VALUES(\'\',:userid,:followerid)', array(':userid' => $userid, ':followerid' => $followerid));
                } else {
                    echo 'Already following';
                    exit();
                }
                $isFollowing = true;
            }
        }

    }
    //Unfollow function
    if (isset($_POST['unfollow'])) {
        if ($userid != $followerid) {
            if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid))) {
                if ($followerid == 3) {
                    DB::query('UPDATE users SET verified=0 WHERE id=:userid', array(':userid' => $userid));
                }
                DB::query('DELETE FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid));
            }
            $isFollowing = false;
        }
    }

    //kapg finafollow na kang user
    if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid' => $followerid))) {
        $isFollowing = true;
    }

    //Post function
    if (isset($_POST['post'])) {
        $postbody = $_POST['postbody'];
        $loggedInUserId = login::isLoggedIn();
        //checking the length of the post
        if (strlen($postbody) < 1 || strlen($postbody) > 160) {
            die('Your post is too long');
        }
        //Pwedi ka lang mag post sa sadiri mong account
        if ($userid == $loggedInUserId){
            DB::query('INSERT INTO posts VALUES(\'\',:postbody, now(), :userid,0)', array(':postbody' => $postbody, ':userid' => $userid));
        } else {
            die('Incorrect user');
        }

    }
    //Like function
    if (isset($_GET['postid'])){
        //checking if the logged in user already liked the post
        if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'],':userid'=>$followerid))){
            //like will insert in the database
            DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid'=>$_GET['postid']));
            //insert the user who liked the post
            DB::query('INSERT INTO post_likes VALUES(\'\',:postid,:userid)',array(':postid'=>$_GET['postid'],':userid'=>$followerid));
        } else {
            DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid'=>$_GET['postid']));
            DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'],'userid'=>$followerid));
        }

    }
    //querying the posts
    $dbposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid' => $userid));
    $posts = "";
    //to print the post
    foreach ($dbposts as $p) {
        //
        if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'],':userid'=>$followerid))){
            $posts .= htmlspecialchars($p['body'])."
                    <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                        <input type='submit' name='like' value='Like'>
                        <span>".$p['likes']."likes</span>
                    </form><br/> <hr/>";
        } else {
            $posts .= htmlspecialchars($p['body'])."
                    <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                        <input type='submit' name='unlike' value='Unlike'>
                        <span>".$p['likes']." likes</span>
                    </form><br/> <hr/>";
        }
    }

} else {
    die('User not found!');
}
?>
<h1><?php echo $username;?>'s Profile<?php if ($verified){echo "- Verified";}?></h1>
<form action="profile.php?username=<?php echo $username; ?>" method="post">
   <?php
    if ($userid != $followerid){
       if ($isFollowing){
           echo '<input type="submit" name="unfollow" value="Unfollow">';
       }else {
           echo '<input type="submit" name="follow" value="Follow">';
       }
   }
   ?>
</form>
<form action="profile.php?username=<?php echo $username;?>" method="post">
    <textarea name="postbody" cols="80" rows="8" required="required"></textarea><br/>
    <input type="submit" name="post" value="Post">
</form>

<div class="posts">
    <?php echo $posts;?>
</div>
