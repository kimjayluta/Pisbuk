<?php
include('./classes/db.php');
include('./classes/log_in.php');

if (login::isLoggedIn()){
    //echo "Logged in! account id:".login::isLoggedIn();
    if (isset($_POST['changepassword'])){

        $oldpassword = $_POST['oldpassword'];
        $newpassword = $_POST['newpassword'];
        $newpasswordrepeat = $_POST['newpasswordrepeat'];
        $userid = login::isLoggedIn();

        if (password_verify($oldpassword,DB::query('SELECT password FROM users WHERE id=:userid', array(':userid'=>$userid))[0]['password'])){

            if ($newpassword == $newpasswordrepeat){

                if (strlen($newpassword) >= 6 && strlen($newpassword) <= 60){

                    DB::query('UPDATE users SET password=:newpassword WHERE id=:userid', array(':newpassword'=>password_hash($newpassword,PASSWORD_BCRYPT),':userid'=>$userid));
                    echo "Password changed successfully!";
                } else {
                    echo "Your password is invalid!";
                }
            } else {
                echo "Password don't match!";
            }
        } else {
            echo "Incorrect old password";
        }
    }
} else {
    die("Not logged in!");
}

?>
<h1>Change password?</h1>
<form action="change_password.php" method="post">
    <input type="password" name="oldpassword" value="" placeholder="Current password ..."><p></p>
    <input type="password" name="newpassword" value="" placeholder="New password ..."><p></p>
    <input type="password" name="newpasswordrepeat" value="" placeholder="Repeat new password ..."><p></p>
    <input type="submit" name="changepassword" value="Change password">
</form>
