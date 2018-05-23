<?php
include('./classes/db.php');
include('./classes/log_in.php');


if (login::isLoggedIn()){
$userid = login::isLoggedIn();
} else {
    die ("Not logged in!");
}

if (isset($_POST['send'])) {
    if (DB::query('SELECT id FROM users WHERE id=:receiver', array(':receiver' => $_GET['receiver']))) {
        DB::query('INSERT INTO messages VALUES (\'\', :body, :sender, :receiver, 0)', array(':body' => $_POST['body'], ':sender' => $userid, ':receiver' => htmlspecialchars($_GET['receiver'])));
        echo 'Messages sent!';
    } else {
        die('Invalid user id!');
    }
}
?>

<h1>Messages</h1>
<form action="send_message.php?receiver=<?php echo htmlspecialchars($_GET['receiver']);?>" method="post">
    <textarea name="body" cols="80" rows="8"></textarea>
    <input type="submit" name="send" value="Send">
</form>