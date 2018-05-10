<?php
include('./classes/db.php');
include('./classes/log_in.php');

if (login::isLoggedIn()){
    echo "Logged in! account id:".login::isLoggedIn();
} else {
    echo "Not logged in!";
}