<?php
include('./classes/db.php');
include('./classes/log_in.php');

$username = "";
$isFollowing = false;


if (isset($_GET['username'])){
    //checking if the username(username ng ifafollow) is in the database
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))){

        $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['username'];
        //id kang ifafollow
        $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['id'];
        //id kang nka login
        $followerid = login::isLoggedIn();

        if (isset($_POST['follow'])){
            if ($userid != $followerid) {
                //kapag dae pa finafollow
                if (!DB::query('SELECT followers_id FROM followers WHERE user_id=:userid', array(':userid' => $userid))) {
                    DB::query('INSERT INTO followers VALUES(\'\',:userid,:followerid)', array(':userid' => $userid, ':followerid' => $followerid));
                } else {
                    echo 'Already following';
                    exit();
                }
                $isFollowing = true;
            }
        }
        if (isset($_POST['unfollow'])){
            if ($userid != $followerid) {
                if (DB::query('SELECT followers_id FROM followers WHERE user_id=:userid', array(':userid' => $userid))) {
                    DB::query('DELETE FROM followers WHERE user_id=:userid AND followers_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid));
                }
                $isFollowing = false;
            }
        }
        //kapg finafollow na kang user
        if (DB::query('SELECT followers_id FROM followers WHERE user_id=:userid', array(':userid'=>$userid))){
            $isFollowing = true;
        }
    } else {
        die('User not found!');
    }
}
?>
<h1><?php echo $username;?>'s Profile</h1>
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
