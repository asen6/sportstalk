<?php
    require('common.php');
?><!doctype html>
<html>
    <head>
        <title>quobit:about</title>
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
            
            .bio_section {
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
            <div class="logo"><a href="/home.php" style="text-decoration: none">quobit</a>:about</div>
        </div>
        
        <div id="middle">
            <div id="about_quobit">
                <div class="section_header">
                    About quobit
                </div>
                <div class="section_content">
                    <p>
                        Watching sports is better on TV, but talking sports is better at the game.  Quobit is here to bridge the gap.
                    </p>
                    <p>
                        7 months ago, we found ourselves watching tons of sports, but thousands of miles away from our college buddies and fans in our home cities.  None of the stuff out there already - ESPN chat, Facebook, Twitter, forums, emails, texting, gchat - let us do exactly what we wanted to do.  Have <strong>real conversations</strong> about live sports, anytime we wanted.  So, we decided to make quobit to talk with our friends and the millions of sports fans across the globe.
                    </p>
                    <p>
                        We're still in beta mode now, so check us out for now during Sunday Night Football only.
                    </p>
                </div>
            </div>
            <br />
            <div id="about_us">
                <div class="section_header">
                    About us
                </div><p />
                <div class="section_content">
                    <div class="bio_section">
                        <strong>Amartya Sengupta</strong> is a Longhorns and Rockets fan from Houston living in Boston.  He graduated from Harvard in 2010 with a degree in Applied Math.
                    </div>
                    <div class="bio_section">
                        <strong>Josh Feng</strong> is a Yankees and Jets fan from New Jersey living in Boston.  He graduated from Harvard in 2010 with a degree in Applied Math.
                    </div>
                    <div class="bio_section">
                        <strong>Peter Salas</strong> is a Cowboys and Yankees fan from New York living in Seattle.  He graduated from Harvard in 2010 with a degree in Computer Science.
                    </div>
                    <div class="bio_section">
                        <strong>Gautam Punukollu</strong> is a Penguins and Steelers fan from Pittsburgh living in New York.  He graduated from Columbia in 2010 with a degree in Economics.
                    </div>
                    <div class="bio_section">
                        <strong>Derek Mueller</strong> is a Ohio State and Reds from Cincinnati living in New York.  He graduated from Harvard in 2010 with a degree in Psychology.
                    </div>
                    <div class="bio_section">
                        <strong>Will Green</strong> is a Liverpool and Rockets fan from Houston living in Galveston.  He graduated from Case Western in 2010 with a degrees in Chemistry and Psychology.
                    </div>
                </div>
            </div><br />
        <div id="bottom_bar">
            <a href="/about.php" id="about">about</a>
            <a href="/privacy.php" id="privacy">privacy</a>
            <a href="/contact.php" id="contact">contact</a>
        </div>
    </body>
</html>