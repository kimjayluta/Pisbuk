<?php
include('./classes/db.php');
include('./classes/log_in.php');
session_start();
if (login::isLoggedIn()){
    $userid = login::isLoggedIn();
} else {
    die ("Not logged in!");
}

$cstrong = true;
$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
if (!isset($_SESSION['token'])){
    $_SESSION['token'] = $token;
}

//SEND MSG function
if (isset($_POST['send'])){
    if (!isset($_POST['nocsrf'])){
        die("INVALID TOKEN!");
    }
    if ($_POST['nocsrf'] != $_SESSION['token']){
        die('INVAlID TOKEN');
    }
    if (DB::query('SELECT id FROM users WHERE  id=:receiver', array(':receiver'=>$_GET['receiver']))){
        DB::query('INSERT INTO messages VALUES(\'\', :body, :sender, :receiver, 0);',array(':body'=>$_POST['body'], ':sender'=>$userid, ':receiver'=>htmlspecialchars($_GET['receiver'])));
        echo 'Message sent!';
    } else {
        die('Invalid User!');
    }
    //session_destroy();
}
?>
<h1>Send a message</h1>
<form action="send-message.php?receiver=<?php echo htmlspecialchars($_GET['receiver']);?>" method="post">
    <textarea name="body" rows="8" cols="80"></textarea><br /><p />
    <input type="hidden" name="nocsrf" value="<?php echo $_SESSION['token']; ?>">
    <input type="submit" name="send" value="Send Message">
</form>