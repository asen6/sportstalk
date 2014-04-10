<?php
    require('common.php');
?><!doctype html>
<html>
    <head>
        <title>quobit:why facebook</title>
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
            
            .section_header {
              font-size:2em;
            }
            
            .section_content {
              font-size:1em;
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
            
            a {
                color:inherit;
                text-decoration:none;
            }
            
            a:hover {
                text-decoration:underline;
            }
            
            .indented {
                text-indent:20px;
            }
        </style>
    </head>
    <body>
        <div id="top">
            <div class="logo"><a href="/home.php" style="text-decoration: none">quobit</a>:why facebook</div>
        </div>
        
        <div id="middle">
            <div class="section_header">
                Why does quobit require Facebook login?
            </div>
            <div class="section_content">
                <p>
                    Facebook login is a core part of the quobit user experience for three reasons.
                </p>
                <p>
                  <div class="indented">(1) It helps us ensure high quality content (people won't spam if their name is on it).</div>
                  <div class="indented">(2) It makes it easy to invite your friends onto the site.</div>
                  <div class="indented">(3) Now you won't have to remember yet another username and password!</div>
                </p>
            </div><br />
            <div class="section_header">
                Should I be afraid?
            </div>
            <div class="section_content">
                <p>
                    No!  Not only would we never (NEVER) abuse your information, we couldn't even if we wanted to.  <strong>Facebook wouldn't let us</strong> - we have to get explicit permission to receive or do anything with your info.
                </p>
                </p>
                    The only things of yours that we store is your username and email.  Your quobit account is linked to your Facebook, but we can only see your basic Facebook info (what anyone searching for you can already see).  We do not have any of your private Facebook info.  The actual login happens on Facebook, so your info is completely secure.
                </p>
                <p>
                    If you ever want to delete your login information from quobit, we'll even let you do that too.  Just email <a style="color:blue;" href="mailto: contact@quobit.com">contact@quobit.com</a> saying so and we'll take you off the list.
                </p>
                <p>
                    See our complete terms of use in our <a style="color:blue;" href="/privacy.php" id="privacy">privacy</a> page.
                </p>
            </div>
        <div id="bottom_bar">
            <a href="/about.php" id="about">about</a>
            <a href="/privacy.php" id="privacy">privacy</a>
            <a href="/contact.php" id="contact">contact</a>
        </div>
    </body>
</html>























