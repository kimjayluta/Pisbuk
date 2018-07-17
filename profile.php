<?php
include('./classes/db.php');
include('./classes/log_in.php');
include('./classes/post.php');
include('./classes/image.php');
include('./classes/notify.php');

$username = "";
$verified = false;
$isFollowing = false;
$userid = "";
$followerid = "";
if (isset($_GET['username'])) {
    //checking if the username(username ng ifafollow) is in the database
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))) {
        //Username ng nsa tig vivisit na profile account
        $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['username'];
        //kinukua ang value kang verified 0 means not verified and 1 means verified account
        $verified = DB::query('SELECT verified FROM users WHERE  username=:username', array(':username' => $_GET['username']))[0]['verified'];
        //kinukua ang id kang nsa link na username
        $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['id'];
        //id kang nka login
        $followerid = login::isLoggedIn();

        //follow function
        if (isset($_POST['follow'])) {
            if ($userid != $followerid) {
                //kapag dae pa finafollow
                if (!DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid,':followerid'=>$followerid))) {
                    if ($followerid == 3) {
                        DB::query('UPDATE users SET verified=1 WHERE id=:userid', array(':userid' => $userid));
                    }
                    DB::query('INSERT INTO followers VALUES(\'\',:userid,:followerid)', array(':userid' => $userid, ':followerid' => $followerid));
                } else {
                    echo 'Already following';
                    exit();
                }
                $isFollowing = true;
            }
        }

    }
    //Unfollow function
    if (isset($_POST['unfollow'])) {
        if ($userid != $followerid) {
            if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid))) {
                if ($followerid == 3) {
                    DB::query('UPDATE users SET verified=0 WHERE id=:userid', array(':userid' => $userid));
                }
                DB::query('DELETE FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid));
            }
            $isFollowing = false;
        }
    }
    //kapg finafollow na kang user
    if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid' => $followerid))) {
        $isFollowing = true;
    }

    //delete post function
    if (isset($_POST['deletePost'])){
        if (DB::query('SELECT id FROM posts WHERE id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid))){
            //Delete the post
            DB::query('DELETE FROM posts WHERE id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid));
            //Delete the like in the post
            DB::query('DELETE FROM post_likes WHERE post_id=:postid', array(':postid'=>$_GET['postid']));
            //Delete comment
            DB::query('DELETE FROM comments WHERE post_id=:postid', array(':postid'=>$_GET['postid']));
            echo 'Post deleted!';
        }
    }

    //Post and Post image function
    if (isset($_POST['post'])) {
        if ($_FILES['postimg']['size'] == 0){
            post::createPost($_POST['postbody'], login::isLoggedIn(), $userid);
        } else {
          $postid = post::createImage($_POST['postbody'], login::isLoggedIn(), $userid);
          image::uploadImg('postimg','UPDATE posts SET postimg=:postimg WHERE id=:postid', array(':postid'=>$postid));
        }
    }

    //Like function
    if (isset($_GET['postid']) && !isset($_POST['deletepost'])){
        post::likePost($_GET['postid'], $followerid);
    }

    //Display function
    $posts = post::postDisplay($userid, $username, $followerid);

} else {
    die('User not found!');
}
?>

<!---->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pisbuk by Kim</title>
    <link rel="stylesheet" type="text/css" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/twbs/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/Footer-Dark.css">
</head>
<body>
<nav class="navbar navbar-light justify-content-between" style="background: #5f5a5d;">
    <form class="form-inline">
        <a href="#" class="navbar-brand" style="color:white;"><strong>Pisbuk</strong></a>
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link active" href="#" style="color:white;">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" style="color:white;">Message</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" style="color:white;">Notification</a>
            </li>
            <li class="nav-item dropdown" >
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" style="color:white;">User</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#">Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#">##</a>
                    <a class="dropdown-item" href="#">##</a>
                    <a class="dropdown-item" href="#">Logout</a>
                </div>
            </li>
        </ul>
        <div class="ml-auto">
            <input class="form-control mr-sm-2" placeholder="Search" aria-label="username" type="text">
            <button class="btn btn-primary" type="button" id="login" data-bs-hover-animate="shake"><strong>Search</strong></button>
        </div>
    </form>
</nav>
<!--Content-->
<div class="container">
    <h1><?php echo $username;?>'s Profile<?php if ($verified){echo "- Verified";}?></h1>
</div>
<div>
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <ul class="list-group">
                    <li class="list-group-item"><span><strong>About Me</strong></span>
                        <p>Welcome to my profile bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;bla bla&nbsp;</p>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group">
                    <div class="timelineposts">

                    </div>
                </ul>
            </div>
            <div class="col-md-3">
                <button class="btn btn-default" type="button" style="width:100%; background-color:#0275d8; color:#fff; padding:16px 32px;margin:0px 0px 6px;border:none;box-shadow:none;text-shadow:none;opacity:0.9;text-transform:uppercase;font-weight:bold;font-size:13px;letter-spacing:0.4px;line-height:1;outline:none;" onclick="showPostModal()">NEW POST</button>
                <ul class="list-group"></ul>
            </div>
        </div>
    </div>
</div>
<!--Modal for comment-->
<div class="modal" id="commentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"  style="max-height: 400px; overflow-y: auto;">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--Modal for new post-->
<div class="modal" id="newPost" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">What's your thought?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                <form action="profile.php?username=<?php echo $username; ?>" method="post" enctype="multipart/form-data">
                    <textarea name="postbody" rows="8" cols="80"></textarea>
                    <br />Upload an image:
                    <input type="file" name="postimg">
            </div>
            <div class="modal-footer">
                <input type="submit" name="post" value="Post" class="btn btn-default" type="button" style="background-image:url(&quot;none&quot;);background-color:#da052b;color:#fff;padding:16px 32px;margin:0px 0px 6px;border:none;box-shadow:none;text-shadow:none;opacity:0.9;text-transform:uppercase;font-weight:bold;font-size:13px;letter-spacing:0.4px;line-height:1;outline:none;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!--Footer-->
<div class="footer-dark">
    <footer>
        <div class="container">
            <p class="copyright">Social Network© 2016</p>
        </div>
    </footer>
</div>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript">
    function scrollToAnchor(aid) {
        var atag = $(aid);
            $('html,body').animate({scrollTop: atag.offset().top},'slow');
    }
 
    $(document).ready(function() {
        $.ajax({
  
            type: "GET",
            url: "api/profileposts?username=<?php echo $username; ?>",
            processData: false,
            contentType: "application/json",
            data: '',
            success: function(r) {
                var posts = JSON.parse(r)
                $.each(posts, function(index) {
                    $('.timelineposts').html(
                        $('.timelineposts').html() +'<li class="list-group-item" id="'+posts[index].PostId+'"><blockquote><p>'+posts[index].PostBody+'</p><footer>Posted by '+posts[index].PostedBy+' on '+posts[index].PostDate+'<button class="btn btn-default" type="button" style="color:#eb3b60;background-image:url(&quot;none&quot;);background-color:transparent;" data-id=\"'+posts[index].PostId+'\"> <i class="glyphicon glyphicon-heart" data-aos="flip-right"></i><span> '+posts[index].Likes+' Likes</span></button><button class="btn btn-default comment" data-postid=\"'+posts[index].PostId+'\" type="button" style="color:#eb3b60;background-image:url(&quot;none&quot;);background-color:transparent;"><i class="glyphicon glyphicon-flash" style="color:#f9d616;"></i><span style="color:#f9d616;"> Comments</span></button></footer></blockquote></li>'
                    )

                    $('[data-postid]').click(function() {
                        var buttonid = $(this).attr('data-postid');

                        $.ajax({

                            type: "GET",
                            url: "api/comments?postid=" + $(this).attr('data-postid'),
                            processData: false,
                            contentType: "application/json",
                            data: '',
                            success: function(r) {
                                var res = JSON.parse(r)
                                showCommentsModal(res);
                            },
                            error: function(r) {
                                console.log(r)
                            }

                        });
                    });

                    $('[data-id]').click(function() {
                        var buttonid = $(this).attr('data-id');
                        $.ajax({

                            type: "POST",
                            url: "api/likes?id=" + $(this).attr('data-id'),
                            processData: false,
                            contentType: "application/json",
                            data: '',
                            success: function(r) {
                                var res = JSON.parse(r)
                                $("[data-id='"+buttonid+"']").html(' <i class="glyphicon glyphicon-heart" data-aos="flip-right"></i><span> '+res.Likes+' Likes</span>')
                            },
                            error: function(r) {
                                console.log(r)
                            }

                        });
                    })
                })
                scrollToAnchor(location.hash);

            },
            error: function(r) {
                console.log(r)
            }

        });

    });
    function showPostModal() {
        $('#newPost').modal('show');

    }

    function showCommentsModal(res) {
        $('#commentsModal').modal('show')
        var output = "";
        for (var i = 0; i < res.length; i++) {
            output += res[i].Comment;
            output += " ~ ";
            output += res[i].CommentedBy;
            output += "<hr />";
        }

        $('.modal-body').html(output)
    }

</script>
</body>
</html>
