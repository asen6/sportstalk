<?php
    require('common.php');
?><!doctype html>
<html>
    <head>
        <title>quobit:contact</title>
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
        </style>
    </head>
    <body>
        <div id="top">
            <div class="logo"><a href="/home.php" style="text-decoration: none">quobit</a>:contact</div>
        </div>
        
        <div id="middle">
            <div class="section_content">
                <p>
                    We love to hear from our users. Please send your questions, comments, and feedback to <a style="color:blue;" href="mailto: contact@quobit.com">contact@quobit.com</a>  or use the "feedback" button on the left side of the screen.
                </p>
                <p>
                    If you want to reach Amartya, Josh, Peter, Gautam, Derek, or Will directly, just email us at first name at quobit dot com.
                </p>
            </div><br />
        <div id="bottom_bar">
            <a href="/about.php" id="about">about</a>
            <a href="/privacy.php" id="privacy">privacy</a>
            <a href="/contact.php" id="privacy">contact</a>
        </div>
    </body>
</html>























