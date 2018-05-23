<?php
include('./classes/db.php');
include('./classes/log_in.php');

if (login::isLoggedIn()){
    $userid = login::isLoggedIn();

} else {
    die ("Not logged in!");
}
//reply function
if (isset($_GET['mid'])){
    $message = DB::query('SELECT * FROM messages WHERE id=:mid', array(':mid'=>$_GET['mid']))[0];
    echo "<h1>View Messages!</h1>";
    echo htmlspecialchars($message['body']);
    echo "<hr />";

    if ($message['sender'] == $userid){
        $id = $message['receiver'];
    } else {
        $id = $message['sender'];
    }
    DB::query('UPDATE messages SET `read`=1 WHERE id=:mid AND receiver=:receiver OR sender=:sender', array(':mid'=>$_GET['mid'], ':receiver'=>$userid, ':sender'=>$userid))
    ?>
    <form action="send-message.php?receiver=<?php echo $id;?>" method="post">
        <textarea name="body" rows="8" cols="80"></textarea><br /><p />
        <input type="submit" name="send" value="Send Message">
    </form>
    <?php
} else  {
?>
<h1>My messages</h1>
<?php
    //messages printing function
    $messages = DB::query('SELECT messages.*, users.username FROM messages, users WHERE receiver=:receiver OR sender=:sender AND users.id = messages.sender', array(':receiver'=>$userid,':sender'=>$userid));
    foreach ($messages as $message) {
        if (strlen($message['body']) > 10) {
            $m = substr($message['body'], 0, 10) . " ...";
        } else {
            $m = $message['body'];
        }
        if ($message['read']) {
            echo "<a href='my-messages.php?mid=".$message['id']."'><strong>".$m."</strong></a> sent by ".$message['username']."<hr />";
        } else {
            echo "<a href='my-messages.php?mid=".$message['id']."'>".$m."</a> sent by ".$message['username']."<hr />";
        }
    }
}
?>