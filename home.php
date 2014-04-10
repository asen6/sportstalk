<?php
    require('common.php');
    $current_event_id = -1;
    $current_event_name = "Event";
    if (db_connect(/*is_connection_required*/false)) {
        $current_event_id = db_get_current_event_id();
        if ($current_event_id != -1) {
            $current_event_name = db_get_event_name($current_event_id);
        }
    }

?><!doctype html>
<html>
    <head>
        <title>quobit</title>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
        <script type="text/javascript" src="http://use.typekit.com/bii3zwb.js"></script>
        <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
        <style>
            body {
                width:960px;
                background-color:#fafafa;
                background-image:url('gradient.png');
                background-repeat:repeat-x;
                margin:1em auto;
                font-family:"alber-new-1","alber-new-2", Lucida Grande, Segoe UI, sans-serif;
                padding:1em;
            }
            
            .logo {
              font-size:32pt;
              font-family: "etica-display-1","etica-display-2";
              font-weight:300;
              float:left;
            }
            
            .logo_version_tag {
              font-size:8pt;
              font-family: "etica-display-1","etica-display-2";
              font-weight:200;
              float:left;
            }
            
            .header {
                font-size:4.3em;
                margin-bottom:0.4em;
            }
            
            .news {
                font-size:1.5em;
                text-align:center;
            }
            
            .follow {
                margin-top:3em;
            }
            
            .header_subtext {
                font-size:1.5em;
            }
            
            .signup_subtext {
                font-size:0.8em;
            }
            
            #top, #middle, #bottom, #bottom_bar {
                overflow:hidden;
                clear:both;
            }
            
            #bottom_bar {
                margin-top:3em;
            }
            
            #bottom_bar a {
                margin-right:3em;
            }
            
            #middle {
                padding-top:2em;
            }
            
            #bottom {
                margin-top:3em;
                color:#888888;
            }
            
            #events {
                color:red;
                font-size:1.5em;
                position:absolute;
                text-align:center;
                padding-left:20px;
                top:100px;
                height:100px;
                width:400px;
            }
            
            .left {
                width:40%;
                float:left;
            }
            
            .right {
                float:right;
            }
            
            h3 {
                margin:0;
                padding-bottom:0.2em;
                margin-bottom:0.5em;
                border-bottom:thin solid;
            }
            
            button {
                border:none;
                border-radius:4px;
                padding:0.5em;
                font-family:inherit;
                font-weight:600;
                cursor:pointer;
            }
            
            #signup {
                display:block;
                color:white;
                width:100%;
                padding:0.5em;
                font-size:14pt;
                margin-top: 10%;
                margin-bottom:2%;
                background: #132232; /* Old browsers */
                background: -moz-linear-gradient(top, #132232 0%, #39516b 100%); /* FF3.6+ */
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#132232), color-stop(100%,#39516b)); /* Chrome,Safari4+ */
                background: -webkit-linear-gradient(top, #132232 0%,#39516b 100%); /* Chrome10+,Safari5.1+ */
                background: -o-linear-gradient(top, #132232 0%,#39516b 100%); /* Opera11.10+ */
                background: -ms-linear-gradient(top, #132232 0%,#39516b 100%); /* IE10+ */
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#132232', endColorstr='#39516b',GradientType=0 ); /* IE6-9 */
                background: linear-gradient(top, #132232 0%,#39516b 100%); /* W3C */
            }
            
            #signin {
                float:right;
                display:block;
            }
            
            a {
                color:inherit;
                text-decoration:none;
            }
            
            a:hover {
                text-decoration:underline;
            }
            
            .right img {
                margin-top:2em;
                margin-right:1.5em;
                width:450px;
                box-shadow: 4px 4px 20px #888;
                -moz-box-shadow: 4px 4px 20px #888;
            }
            
            .social a {
                text-decoration:none;
            }
            
            .social img {
                width:20px;
                border:0;
                vertical-align:middle;
            }
        </style>
        <script type="text/javascript">                    
            function quobit_login(access_token) {
                $.getJSON("login.php", "accesstoken=" + access_token, function(data, status, xhr) {
                    if (data[0] > 0) {
                        <?php if ($current_event_id != -1) { ?>
                        window.location.href = "index.php";
                        <?php } else { ?>
                        alert("Thanks for signing up! Check back when we're live on Sunday night.");
                        <?php } ?>
                    } else {
                        alert(data[1]);
                    }
                });
            }
            
            function sign_up() {
                FB.getLoginStatus(function(response) {
                    if (response.authResponse) {
                        FB.api('/me/permissions', function(perms) {
                            if (perms.data[0].email) {
                                quobit_login(response.authResponse.accessToken);
                            } else {
                                facebook_login_with_permissions();
                            }
                        });
                    } else {
                        facebook_login_with_permissions();
                    }
                });
            }
            
            function facebook_login_with_permissions() {
                FB.login(
                    function(login_response) {
                        if (login_response.authResponse) {
                            quobit_login(login_response.authResponse.accessToken);
                        } else {
                            // They rejected or dismissed the dialog.
                        }
                    },
                    {scope: "email"}
                );
            }
        </script>
    </head>
    <body>
        <div id="fb-root"></div>
        <script>
          window.fbAsyncInit = function() {
            FB.init({
              appId  : '<?= FACEBOOK_APPID ?>',
              status : true, // check login status
              cookie : true, // enable cookies to allow the server to access the session
              xfbml  : true,  // parse XFBML
              oauth : true //enables OAuth 2.0
            });
          };

          (function() {
            var e = document.createElement('script');
            e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
            e.async = true;
            document.getElementById('fb-root').appendChild(e);
          }());
        </script>
        <div id="top">
            <div class="logo"><a href="/home.php" style="text-decoration: none">quobit</a></div>
            <div class ="logo_version_tag">beta</div>
            <div id="signin"><a href="#signin" onclick="sign_up(); return false;">Sign In</a></div>
        </div>
        
        <div id="middle">
            <div class="left">
                <div class="header">
                    Bring the crowd to you
                </div>
                <div class="header_subtext">
                    Wherever you are, talk live sports with friends and fans here on quobit.
                </div>
                <div>
                    <button id="signup" onclick="sign_up();">Sign Up</button>
                </div>
                <div class="signup_subtext">
                    <a style="color:blue;" href="/why_facebook.php">Why does quobit require Facebook login?</a>
                </div>
                
            </div>
            
            <div class="right">
                <?php if ($current_event_id != -1) { ?>
                <div id="events">
                    <a href="#" id="about" onclick="sign_up(); return false;">Live Now! <?= $current_event_name ?></a>
                </div>
                <?php } ?>
                <img src="quobit.png" />
            </div>
        </div>
        <div id="bottom">
            <div class="news">
                We'll be live on Sundays during the 2011 NFL season.
                <span class="social">
                    In the meantime, follow us on 
                    <a href="http://www.facebook.com/apps/application.php?id=263231240369269">
                        <img src="facebook.png" />
                    </a>
                    or 
                    <a href="http://twitter.com/quobitSports">
                        <img src="twitter.png" />
                    </a>
                    .
                </span>
            </div>
        </div>
        <div id="bottom_bar">
            <a href="/about.php" id="about">about</a>
            <a href="/privacy.php" id="privacy">privacy</a>
            <a href="/contact.php" id="contact">contact</a>
        </div>
    </body>
</html>