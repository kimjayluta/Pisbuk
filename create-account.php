<?php
include('classes/db.php');
if (isset($_POST['createaccount'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email    = $_POST['email'];
    //Checking if the username is already exist in the database
    if (!DB::query('SELECT username FROM users  WHERE username=:username', array(':username'=>$username))){
        //checking if the username is too short or too long
        if (strlen($username) >= 3 && strlen($username) <= 32){
            //checking if the username is a valid characters
            if (preg_match('/[a-zA-Z0-9_]+/', $username)){
                //checking if the password is too long and too short
                if (strlen($password) >= 6 && strlen($password) <= 60 ){
                    //checking if email is valid
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)){
                        //Inserting the user inputs data into the database
                        DB::query('INSERT INTO users VALUES (\'\',:username,:password,:email)', array(':username'=>$username, ':password'=>password_hash($password,PASSWORD_BCRYPT), ':email'=>$email));
                        echo "Success!";
                    } else {
                        echo "Your email is invalid!";
                    }
                } else {
                    echo "Your password is invalid!";
                }
            } else    {
                echo "Invalid characters!";
            }
        } else {
            echo "Your username is invalid!";
        }
    } else {
        echo "Username already exist!";
    }
}
?>

<h1>Register</h1>
<form action="create-account.php" method="post">
    <input type="text" name="username" placeholder="Username..." value="">       <p></p>
    <input type="password" name="password" placeholder="Password..." value="">   <p></p>
    <input type="email" name="email" placeholder="someone@somesite.com" value=""><p></p>
    <input type="submit" name="createaccount" value="Create account">             <p></p>
</form>