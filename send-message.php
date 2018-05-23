<?php
include('./classes/db.php');
include('./classes/log_in.php');

if (login::isLoggedIn()){
    $userid = login::isLoggedIn();

} else {
    die ("Not logged in!");
}
//SEND MSG function
if (isset($_POST['send'])){
    if (DB::query('SELECT id FROM users WHERE  id=:receiver', array(':receiver'=>$_GET['receiver']))){
        DB::query('INSERT INTO messages VALUES(\'\', :body, :sender, :receiver, 0);',array(':body'=>$_POST['body'], ':sender'=>$userid, ':receiver'=>htmlspecialchars($_GET['receiver'])));
        echo 'Message sent!';
    } else {
        die('Invalid User!');
    }
}
?>
<h1>Send a message</h1>
<form action="send-message.php?receiver=<?php echo htmlspecialchars($_GET['receiver']);?>" method="post">
    <textarea name="body" rows="8" cols="80"></textarea><br /><p />
    <input type="submit" name="send" value="Send Message">
</form>