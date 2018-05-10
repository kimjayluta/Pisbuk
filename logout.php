<?php
include('classes/db.php');
include('classes/log_in.php');

if (!login::isLoggedIn()){
    die("Not logged in!");
}

if (isset($_POST['confirm'])){
    if (isset($_POST['alldevices'])){
        DB::query('DELETE FROM login_tokens WHERE user_id=:userid',array(':userid'=>login::isLoggedIn()));
    }else {
        if (isset($_COOKIE['SNID'])){
            DB::query('DELETE FROM login_tokens WHERE token=:token',array(':token'=>sha1($_COOKIE['SNID'])));
        }
        setcookie('SNID', 1,time()-3600);
        setcookie('SNID_', 1,time()-3600);
    }
}
?>
<h1>Log out your Account?</h1>
<p>Are you sure to logout your account?</p>
<form action="logout.php" method="post">
    <input type="checkbox" name="alldevices" value="alldevices"> Logout all of your devices? <br /><p />
    <input type="submit" name="confirm" value="Confirm">
</form>
