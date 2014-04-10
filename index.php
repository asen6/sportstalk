<?php

require("common.php");

?><!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml"
  xmlns:fb="https://www.facebook.com/2008/fbml">
<head>
    <title>quobit</title>
    <link rel="stylesheet" href="css/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
    <link rel="stylesheet" href="css/index.css" type="text/css" media="screen" title="no title" charset="utf-8">
    <script src="js/jquery-1.5.2.js" type="text/javascript" charset="utf-8"></script>
    <script src="js/index.js" type="text/javascript" charset="utf-8"></script>
    <script src="js/post.js" type="text/javascript" charset="utf-8"></script>
    <script src="js/debug.js" type="text/javascript" charset="utf-8"></script>    
    <script type="text/javascript" src="http://use.typekit.com/bii3zwb.js"></script>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
    
    <?php 
    $original_post = $_GET["auto"];
    if (DEVELOPMENT_ENVIRONMENT && $original_post == "on") { ?>
    <script src="js/bot.js" type="text/javascript" charset="utf-8"></script>
    <?php } ?>
    
    <script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
    <script type="text/javascript">
    <?php
    
    // Get the username.
    db_connect(/*is_connection_required*/true);
    $user_id = check_user_id(/*is_login_required*/false);
    echo "user_id = $user_id;";
    
    if ($user_id != -1) {
        $facebook_id = db_get_facebook_id_from_id($user_id);
        $username = db_get_username($facebook_id);
        
        echo "facebook_id = $facebook_id;";
        echo "username = \"$username\";";
    }
    
    $invitation = $_GET["invitation"];
    $invitation = htmlentities($invitation);
    if ($invitation != "") {
        echo "invitation = \"$invitation\";";
    }
    
    $event_id = db_get_current_event_id();
    if ($event_id !== null) {
        if ($event_id != -1) {
            $event_name = db_get_event_name($event_id);
            echo "event_name = \"$event_name\";";
        }
        echo "event_id = $event_id;";
    }
    
    ?>
    </script>
</head>
<body>
    <div id="fb-root"></div>
    <script type="text/javascript">
        window.fbAsyncInit = function() {
            FB.init({appId: '<?= FACEBOOK_APPID ?>', status: true, cookie: true, xfbml: true});
            checkfbloginstatus(); 

            if (event_id != null && event_id != -1) {
                postToFeed();             
            }
        };
        (function() {
            var e = document.createElement('script');
            e.type = 'text/javascript';
            e.src = document.location.protocol +
                '//connect.facebook.net/en_US/all.js';
            e.async = true;
            document.getElementById('fb-root').appendChild(e);
        }());
    </script>

    <div id="login_container">
        <div id="login_overlay"></div>
        <div id="login">
            <div id="fb_form">
                <p id="fb_error">Please log in with your facebook account.</p>
                <div id="fb_login_part"> <fb:login-button on-login="fblogin();" perms="email">Login with Facebook</fb:login-button> </div>
            </div>
            <div id="email_form" style="display:none;">
                <p>
                    We are currently in a limited testing phase.  If you provide<br /> your e-mail address,
                    we'll send you an invitation soon!
                </p>
                <div id="email_table">
                    Email:&nbsp;<input id="email" type="text" />&nbsp;<button id="request_invite">Sign Up!</button>
                </div>
            </div>
        </div>
        
    </div>
    
    <div id="help_container">
        <div id="help_overlay"></div>
        <div id="help_content">
            <div id="help_close"><a href="#hide" onclick="close_help_handler(event); return false;" style="text-decoration: none;" >( x )</a></div>
            <div id="help_post_feed">
                <div class="help_title">The Post Feed (left side)</div>
                <p>Post any observations, questions, or random topics here, so long as you have enough points.</p>
            </div><br />
            <div id="help_reply_box">
                <div class="help_title">The Reply Box (right side)</div>
                <p>Think of these like group chat rooms about each post.  Just click on the post you want to discuss and start talking!</p>
            </div><br />
            <div id="help_points">
                <div class="help_title">The Points System</div>
                <p>Earn points by "contributing positively."  Posting, replying, liking - these are just some of the ways you can do it.  Check out the leaderboard to see if you've earned more points than your friends.</p>
                <p>Use points to make posts. Don't be shy - these expire at the end of each game! (Points spent making posts will NOT affect your leaderboard score)</p>
            </div>
        </div>
    </div>
    
    <div class="topfixed">
        <div class="topbar">
            <div id="topbar_left">
                <span class="logo"><a href="/home.php" style="text-decoration: none">quobit</a></span>
                <span class ="logo_version_tag">beta</span>
            </div>
            <div id="topbar_right">
              <a id="help_link" onclick="show_help_overlay(1); return false;">help</a>
              <a id="logout_link" onclick="logout(); return false;">logout</a>
            </div>
        </div>
        <div id="eventbar">
            <span id="active_users"></span>
            <span id="point_count"></span>
            <span id="post_cost"></span>
            <div style="position:relative;display:inline-block;">
                <a id="leaderboard_link" href="#leaders">Leaderboard <span style="font-size:0.6em">&#9660;</span></a>
                <div style="position:absolute;">
                    <div id="leaderboard">ˆ
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="main">
        <div id="feed">
            <div id="comment_box"><textarea id="comment"></textarea></div>
            <div id="button_row"><button id="post">post</button></div>
            <div id="pre_spacer"></div>
            <div id="post_msg" align="left"></div>
            <div id="msgs">
            </div>
        </div>
        
        <div id="chat">
            <div id="chat_content">
                <div id="replies_spacer">
                    <div id="replies_scroller">
                        <div id="replies">
                        </div>
                    </div>
                </div>
                <div id="reply_box">
                    <input id="reply_text" type="text" /><button id="reply_button">reply</button>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <script type="text/javascript" charset="utf-8">
      var is_ssl = ("https:" == document.location.protocol);
      var asset_host = is_ssl ? "https://s3.amazonaws.com/getsatisfaction.com/" : "http://s3.amazonaws.com/getsatisfaction.com/";
      document.write(unescape("%3Cscript src='" + asset_host + "javascripts/feedback-v2.js' type='text/javascript'%3E%3C/script%3E"));
    </script>

    <script type="text/javascript" charset="utf-8">
      var feedback_widget_options = {};

      feedback_widget_options.display = "overlay";  
      feedback_widget_options.company = "quobit";
      feedback_widget_options.placement = "left";
      feedback_widget_options.color = "#132232";
      feedback_widget_options.style = "idea";

      var feedback_widget = new GSFN.feedback_widget(feedback_widget_options);
    </script>
</body>
</html>
