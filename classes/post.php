<?php
 class post {
     //Post function
    public static function createPost($postbody, $loggedInUserId, $profileuserid){
        //checking the length of the post
        if (strlen($postbody) < 1 || strlen($postbody) > 160) {
            die('Your post is too long');
        }
        $topics  = self::getTopics($postbody);
        //Pwedi ka lang mag post sa sadiri mong account
        if ($profileuserid == $loggedInUserId){
            if (count(self::notify($postbody)) != 0){
                foreach (self::notify($postbody) as $key => $n){
                    $s = $loggedInUserId;
                    $r = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$key))[0]['id'];
                    if ($r != 0){
                        DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>$n['type'],':receiver'=>$r,':sender'=>$s, ':extra'=>$n['extra']));
                    }
                }
            }
            DB::query('INSERT INTO posts VALUES(\'\', :postbody, now(), :userid,0, \'\', :topics)', array(':postbody'=>$postbody,':userid'=>$profileuserid,':topics'=>$topics));
        } else {
            die('Incorrect user');
        }
    }

    //Image upload function
     public static function createImage($postbody, $loggedInUserId, $profileuserid){
         //checking the length of the post
         if (strlen($postbody) > 160) {
             die('Your post is too long');
         }
         $topics  = self::getTopics($postbody);
         //Pwedi ka lang mag post sa sadiri mong account
         if ($profileuserid == $loggedInUserId){
             if (count(self::notify($postbody)) != 0){
                 foreach (self::notify($postbody) as $key => $n){
                     $s = $loggedInUserId;
                     $r = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$key))[0]['id'];
                     if ($r != 0){
                         DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type'=>$n['type'],':receiver'=>$r,':sender'=>$s, ':extra'=>$n['extra']));
                     }
                 }
             }
         } else {
             die('Incorrect user');
         }
     }

     //Like function
    public static function likePost($postId, $likerId){
        //checking if the logged in user already liked the post
        if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postId,':userid'=>$likerId))){
            //like will insert in the database
            DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid'=>$postId));
            //insert the user who liked the post
            DB::query('INSERT INTO post_likes VALUES(\'\',:postid,:userid)',array(':postid'=>$postId,':userid'=>$likerId));
        } else {
            DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid'=>$_GET['postid']));
            DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postId,'userid'=>$likerId));
        }
    }

    //hastag function
     public static function getTopics($text) {
         $text = explode(" ", $text);
         $topics = "";
         foreach ($text as $word) {
             if (substr($word, 0, 1) == "#") {
                 $topics .= substr($word, 1).",";
             }
         }
         return $topics;
     }

    //mention function
    public static function link_add($text){
        $text = explode(" ", $text);
        $newstring = "";
        foreach ($text as $word) {
            if (substr($word, 0, 1) == "@") {
                $newstring .= "<a href='profile.php?username=".substr($word, 1)."'>".htmlspecialchars($word)."</a> ";
            } else if (substr($word, 0, 1) == "#") {
                $newstring .= "<a href='topic.php?topic=".substr($word, 1)."'>".htmlspecialchars($word)."</a> ";
            } else {
                $newstring .= htmlspecialchars($word)." ";
            }
        }
        return $newstring;
    }

    public static function notify($text){
        $text = explode(" ",$text);
        $notify = array();
        foreach ($text as $word) {
            if (substr($word, 0, 1) == "@") {
                $notify[substr($word, 1)] = array("type"=>1,"extra"=>' { "postbody":"'.htmlentities(implode($text," ")).'"}');
            }
        }
        return $notify;
    }

    //display function
    public static function postDisplay($userid, $username, $loggedInUserId){
        //querying the posts
        $dbposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid' => $userid));
        $posts = "";

        //to print the post
        foreach ($dbposts as $p) {
            //checking if user already like the post
            if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'],':userid'=>$loggedInUserId))){
                $posts .= "<img src='".$p['postimg']."'><br />".self::link_add($p['body'])."
                        <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                            <input type='submit' name='like' value='Like'>
                            <span>".$p['likes']."likes   </span>";
                        if ($userid == $loggedInUserId){
                            $posts .= "<input type='submit' name='deletePost' value='Delete' />";
                        }
                        $posts .= "
                        </form><br/> <hr/>";
            } else {
                $posts .= "<img src='".$p['postimg']."'>".self::link_add($p['body'])."
                        <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                            <input type='submit' name='unlike' value='Unlike'>
                            <span>".$p['likes']." likes </span>";
                        if ($userid == $loggedInUserId){
                            $posts .= "<input type='submit' name='deletePost' value='Delete' /> </form><br/> <hr/>";
                        }
            }
        }
        return $posts;
    }
}