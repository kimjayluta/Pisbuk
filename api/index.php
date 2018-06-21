<?php
require_once("DB.php");

$db = new DB("127.0.0.1", "socialnetwork", "root", "");

if ($_SERVER['REQUEST_METHOD'] == "GET") {

        if ($_GET['url'] == "auth") {

        } else if ($_GET['url'] == "users") {

        } else if ($_GET['url'] == "posts") {
        //Posting posts
            $token = $_COOKIE['SNID'];
            $userid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];

            //To print the posts from database ,ang ma print na post dgd kung kisay ang finafollow lang kang nka login na user
            $followingposts = $db->query('SELECT posts.body, posts.posted_at, posts.likes, posts.id, users.`username` FROM users,posts,followers  
                                WHERE posts.user_id = followers.user_id   
                                AND users.id = posts.user_id  
                                AND follower_id =:userid ORDER BY posts.likes DESC ;', array(':userid'=>$userid));
//
//            $response = "[";
//            foreach($followingposts as $post) {
//
//                $response .= "{";
//                    $response .= '"PostId": '.$post['id'].',';
//                    $response .= '"PostBody": "'.$post['body'].'",';
//                    $response .= '"PostedBy": "'.$post['username'].'",';
//                    $response .= '"PostDate": "'.$post['posted_at'].'",';
//                    $response .= '"Likes": '.$post['likes'].'';
//                $response .= "},";
//
//
//            }
//            $response = substr($response, 0, strlen($response)-1);
//            $response .= "]";

            $response = array();
            foreach($followingposts as $post) {
                $content = array();
                $content["PostId"] = $post['id'];
                $content["PostBody"] = $post['body'];
                $content["PostedBy"] = $post['username'];
                $content["PostDate"] = $post['posted_at'];
                $content["Likes"] = $post['likes'];

                array_push($response, $content);
            }

            http_response_code(200);
            echo json_encode($response);

        }

} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if ($_GET['url'] == "users"){
            $postBody = file_get_contents("php://input");
            $postBody = json_decode($postBody);

            $username = $postBody->username;
            $password = $postBody->password;
            $email = $postBody->email;
            if (!$db->query('SELECT username FROM users  WHERE username=:username', array(':username'=>$username))){
                //checking if the username is too short or too long
                if (strlen($username) >= 3 && strlen($username) <= 32){
                    //checking if the username is a valid characters
                    if (preg_match('/[a-zA-Z0-9_]+/', $username)){
                        //checking if the password is too long and too short
                        if (strlen($password) >= 6 && strlen($password) <= 60 ){
                            //checking if email is valid
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)){
                                //checking if the email is already used in the database
                                if (!$db->query('SELECT email FROM users WHERE email=:email', array(':email'=>$email))){
                                    //Inserting the user inputs data into the database
                                    $db->query('INSERT INTO users VALUES (\'\',:username,:password,:email,\'0\',\'\')', array(':username'=>$username, ':password'=>password_hash($password,PASSWORD_BCRYPT), ':email'=>$email));
                                    //Mail::sendEmail('Welcome to my social network!', 'Your account has been created', $email);
                                    echo '{ "success": "New account created!" }';
                                    http_response_code(200);
                                } else {
                                    echo '{ "error": "Email is already in used!" }';
                                    http_response_code(409);
                                }
                            } else {
                                echo '{ "error": "Your email is invalid!" }';
                                http_response_code(409);
                            }
                        } else {
                            echo '{ "error": "Your password is invalid only 6 to 60 characters only!" }';
                            http_response_code(409);
                        }
                    } else {
                        echo '{ "error": "Invalid characters!" }';
                        http_response_code(409);
                    }
                } else {
                    echo '{ "error": "Only 3 to 32 characters only!" }';
                    http_response_code(409);
                }
            } else {
                echo '{ "error": "Username already exist!" }';
                http_response_code(409);
            }
        }
        if ($_GET['url'] == "auth") {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $username = $postBody->username;
                $password = $postBody->password;

                if ($db->query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))) {
                        if (password_verify($password, $db->query('SELECT password FROM users WHERE username=:username', array(':username'=>$username))[0]['password'])) {
                                $cstrong = True;
                                $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                                $user_id = $db->query('SELECT id FROM users WHERE username=:username', array(':username'=>$username))[0]['id'];
                                $db->query('INSERT INTO login_tokens VALUES (\'\', :token, :user_id)', array(':token'=>sha1($token), ':user_id'=>$user_id));
                                echo '{ "Token": "'.$token.'" }';
                        } else {
                                echo '{ "error": "Invalid username or password!" }';
                                http_response_code(401);
                        }
                } else {
                        echo '{ "error": "Invalid username or password!" }';
                        http_response_code(401);
                }

        } else if ($_GET['url'] == "likes"){
            $postId = $_GET['id'];
            $token = $_COOKIE['SNID'];
            $likerId = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
            //checking if the logged in user already liked the post
            if (!$db->query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postId,':userid'=>$likerId))){
                //like will insert in the database
                $db->query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid'=>$postId));
                //insert the user who liked the post
                $db->query('INSERT INTO post_likes VALUES(\'\',:postid,:userid)',array(':postid'=>$postId,':userid'=>$likerId));
                //notify::notif("",$postId);
            } else {
                $db->query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid'=>$_GET['postid']));
                $db->query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postId,'userid'=>$likerId));
            }
            echo "{";
            echo '"Likes":';
            echo $db->query('SELECT likes FROM posts WHERE id=:postId',array(':postId'=>$postId))[0]['likes'];
            echo "}";
        }

}  else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
        if ($_GET['url'] == "auth") {
                if (isset($_GET['token'])) {
                        if ($db->query("SELECT token FROM login_tokens WHERE token=:token", array(':token'=>sha1($_GET['token'])))) {
                                $db->query('DELETE FROM login_tokens WHERE token=:token', array(':token'=>sha1($_GET['token'])));
                                echo '{ "Status": "Success" }';
                                http_response_code(200);
                        } else {
                                echo '{ "Error": "Invalid token" }';
                                http_response_code(400);
                        }
                } else {
                        echo '{ "Error": "Malformed request" }';
                        http_response_code(400);
                }
        }
} else {
        http_response_code(405);
}
?>
