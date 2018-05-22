<?php
include('./classes/db.php');
include('./classes/log_in.php');

if (login::isLoggedIn()){
    $userid = login::isLoggedIn();

} else {
    die ("Not logged in!");
}
echo "<h1>Notification</h1>";

if (DB::query('SELECT * FROM notifications WHERE receiver=:userid', array(':userid'=>$userid))){
    $notification = DB::query('SELECT * FROM notifications WHERE receiver=:userid ORDER BY id DESC', array(':userid'=>$userid));
    foreach ($notification as $n){
        if ($n['type']  == 1){
            $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array('senderid'=>$n['sender']))[0]['username'];
            if ($n['extra'] == ""){
                echo "You got a notification!<hr />";
            } else {
                $extra = json_decode($n['extra']);
                echo $senderName." mentioned you in a post!".$extra->postbody."<hr />";
            }
        } elseif ($n['type'] == 2) {
            $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array('senderid' => $n['sender']))[0]['username'];
            echo $senderName . " liked your post!<hr />";
        }
    }
}