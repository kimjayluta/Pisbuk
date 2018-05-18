<?php
 class post {
    public static function createPost($postbody, $loggedInUserId, $profileuserid){
        //checking the length of the post
        if (strlen($postbody) < 1 || strlen($postbody) > 160) {
            die('Your post is too long');
        }
        //Pwedi ka lang mag post sa sadiri mong account
        if ($profileuserid == $loggedInUserId){
            DB::query('INSERT INTO posts VALUES(\'\',:postbody, now(), :userid,0)', array(':postbody'=>$postbody,':userid'=>$profileuserid));
        } else {
            die('Incorrect user');
        }
    }

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

    public static function postDisplay($userid, $username, $loggedInUserId){
        //querying the posts
        $dbposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid' => $userid));
        $posts = "";
        //to print the post
        foreach ($dbposts as $p) {
            //
            if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'],':userid'=>$loggedInUserId))){
                $posts .= htmlspecialchars($p['body'])."
                    <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                        <input type='submit' name='like' value='Like'>
                        <span>".$p['likes']."likes</span>
                    </form><br/> <hr/>";
            } else {
                $posts .= htmlspecialchars($p['body'])."
                    <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                        <input type='submit' name='unlike' value='Unlike'>
                        <span>".$p['likes']." likes</span>
                    </form><br/> <hr/>";
            }
        }
        return $posts;
    }
}