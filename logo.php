<?php
    require('common.php');
?><!doctype html>
<html>
    <head>
        <title>quobit</title>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
        <script type="text/javascript" src="http://use.typekit.com/bii3zwb.js"></script>
        <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
        <style>
            body {
                width:950px;
                background-color:#132232;
                margin:1em auto;
                font-family:"alber-new-1","alber-new-2", Lucida Grande, Segoe UI, sans-serif;
                padding:1em;
            }
            
            .logo {
              color:#fff;
              font-size:256pt;
              font-family: "etica-display-1","etica-display-2";
              font-weight:300;
              float:left;
              padding-top:0em;
              padding-bottom:0.1em;
              padding-left:0.1em;
              padding-right:0.1em;
              border-color:#fff;
              border: thin solid;
            }
            
            .header {
                font-size:2.5em;
            }
            
            .news {
                margin-top:3em;
            }
            
            .follow {
                margin-top:3em;
            }
            
            .header_subtext {
                font-size:0.8em;
            }
            
            #top, #middle, #bottom_bar {
                overflow:hidden;
                clear:both;
            }
            
            #bottom_bar {
                display:none;
                margin-top:3em;
            }
            
            #bottom_bar a {
                margin-right:3em;
            }
            
            #middle {
                padding-top:2em;
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
                margin-top: 12%;
                margin-bottom:5%;
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
                margin-right:1.5em;
                width:450px;
                box-shadow: 4px 4px 20px #888;
            }
            
        </style>
        
    </head>
    <body>
        <div id="fb-root"></div>
        
        <div id="top">
            <div class="logo">qb</div>
            <div id="signin"><a href="#signin" onclick="sign_up(); return false;">Sign In</a></div>
        </div>
        
        
    </body>
</html>