<?php
include('./classes/db.php');
include('./classes/log_in.php');
include('./classes/post.php');
include('./classes/image.php');

$username = "";
$verified = false;
$isFollowing = false;
$userid = "";
$followerid = "";

if (login::isLoggedIn()){
    echo "Logged in! account id:".login::isLoggedIn();
} else {
    die ("Not logged in!");
}

if (isset($_GET['username'])) {
    //checking if the username(username ng ifafollow) is in the database
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))) {
        //Username ng nsa tig vivisit na profile account
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
    //Post and Post image function
    if (isset($_POST['post'])) {
        if ($_FILES['postimg']['size'] == 0){
            post::createPost($_POST['postbody'], login::isLoggedIn(), $userid);
        } else {
          $postid = post::createImage($_POST['postbody'], login::isLoggedIn(), $userid);
          image::uploadImg('postimg','UPDATE posts SET postimg=:postimg WHERE id=:postid', array(':postid'=>$postid));
        }
    }
    //Like function
    if (isset($_GET['postid'])){
        post::likePost($_GET['postid'], $followerid);
    }
    //Display the posts function
    $posts = post::postDisplay($userid, $username, $followerid);

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
<form action="profile.php?username=<?php echo $username;?>" method="post" enctype="multipart/form-data">
    <textarea name="postbody" cols="80" rows="8"></textarea><br /><p />
    Upload an image:
    <input type="file" name="postimg">
    <input type="submit" name="post" value="Post">
</form>

<div class="posts">
    <?php echo $posts;?>
</div>
