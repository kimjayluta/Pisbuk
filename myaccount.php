<?php
include('./classes/db.php');
include('./classes/log_in.php');
if (login::isLoggedIn()){
    $userid = login::isLoggedIn();
} else {
    die ("Not logged in!");
}
if (isset($_POST['uploadimg'])){
    $image = base64_encode(file_get_contents($_FILES['profileimg']['tmp_name']));
    $imgUrl = 'https://api.imgur.com/3/image';
    $options = array('http'=>array(
        'method'=>"POST",
        'header'=>"Authorization: Bearer e8dea8e7d7d5e63ce6a4eff78276ff52a1f9abd3\n".
            "Content-Type: application/x-www-form-urlencoded",
        'content'=>$image
    ));
    if ($_FILES['profileimg']['size'] > 10240000){
        die('Image file is too big!, must be 10mb or less!');
    }
    $context = stream_context_create($options);
    $response =  file_get_contents($imgUrl, false, $context);
    $response = json_decode($response);
    echo '<pre>';
    print_r($response);
    echo '</pre>';
    DB::query('UPDATE users SET profileimg = :profileimg WHERE id=:userid', array(':profileimg'=>$response->data->link, ':userid'=>$userid));
}
?>
<h1>My Account</h1>
<form action="myaccount.php" method="post" enctype="multipart/form-data">
    Upload an image:
    <input type="file" name="profileimg">
    <input type="submit" name="uploadimg" value="Upload">
</form>