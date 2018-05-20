<?php
include('./classes/db.php');
include('./classes/log_in.php');
include('./classes/image.php');
if (login::isLoggedIn()){
    $userid = login::isLoggedIn();
} else {
    die ("Not logged in!");
}
if (isset($_POST['uploadimg'])){
   image::uploadImg('profileimg','UPDATE users SET profileimg = :profileimg WHERE id=:userid', array(':userid'=>$userid));
}
?>
<h1>My Account</h1>
<form action="myaccount.php" method="post" enctype="multipart/form-data">
    Upload an image:
    <input type="file" name="profileimg">
    <input type="submit" name="uploadimg" value="Upload">
</form>