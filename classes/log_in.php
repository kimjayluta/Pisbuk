<?php
//This function uses to check if the user is login or not
class login{
    public static function isLoggedIn(){
        if (isset($_COOKIE['SNID'])){
            //This condition checks if the token is valid in the database
            if (DB::query('SELECT user_id FROM login_tokens WHERE token=:token',array(':token'=>sha1($_COOKIE['SNID'])))){
                $userid = DB::query('SELECT user_id FROM login_tokens WHERE token=:token',array(':token'=>sha1($_COOKIE['SNID'])))[0]['user_id'];
                //checking if the 3days cookie token is still set
                if (isset($_COOKIE['SNID_'])){
                    return $userid;
                } else {
                    //Then if it not set the user will be given a new token for security
                    $cstrong = true;
                    $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                    //Inserting a new token
                    DB::query('INSERT INTO login_tokens VALUES (\'\',:token, :user_id)', array(':token'=>sha1($token),':user_id'=>$userid));
                    //Deleting the old token
                    DB::query('DELETE FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['SNID'])));

                    setcookie("SNID", $token, time() + 60 * 60 * 24 * 7, '/', NULl, NULL, TRUE);
                    setcookie("SNID_", "1",time() + 60 * 60 * 24 * 3, '/', NULl, NULL, TRUE);

                    return $userid;
                }
            }
        }
        return false;                                           }
}