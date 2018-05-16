<?php
include('./classes/db.php');
include('./classes/log_in.php');

$username = "";
$verified = false;
$isFollowing = false;
$userid = "";
$followerid = "";


if (isset($_GET['username'])) {
    //checking if the username(username ng ifafollow) is in the database
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))) {

        $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['username'];
        $verified = DB::query('SELECT verified FROM users WHERE  username=:username', array(':username' => $_GET['username']))[0]['verified'];
        //id kang ifafollow
        $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['id'];
        //id kang nka login
        $followerid = login::isLoggedIn();

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

    if (isset($_POST['post'])) {
        $postbody = $_POST['postbody'];
        $userid = login::isLoggedIn();
        //checking the length of the post
        if (strlen($postbody) < 1 || strlen($postbody) > 160) {
            die('Your post is too long');
        }
        DB::query('INSERT INTO posts VALUES(\'\',:postbody, now(), :userid,0)', array(':postbody' => $postbody, ':userid' => $userid));
    }
    $dbposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid' => $userid));
    $posts = "";
    foreach ($dbposts as $p) {
        $posts .= $p['body'] . "<br/>" . "<hr/>";
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
