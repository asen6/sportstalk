<?php
require("common.php");
db_connect(/*is_connection_required*/true);
require_superuser_access();
?>

<!DOCTYPE html>
<html>
<head>
    <title>quobit tweets</title>
    
    <style>
    .content 
    {
        margin-left:3em;
    }
    .retweet_count 
    {
        margin-left:3em;
    }
    .add_link 
    {
        margin-left:3em;
    }
    .postedby
    {
        font-weight:bold;
    }
    </style>
    
    <link rel="stylesheet" href="css/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
    <link rel="stylesheet" href="css/index.css" type="text/css" media="screen" title="no title" charset="utf-8">
    <script src="js/jquery-1.5.2.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">
    
    function Tweet(text,author,dummy_id)
    {
        this.text = text;
        this.author = author;
        this.dummy_id = dummy_id;
    }
    
    var tweets = {};
    
    <?php
        $event_id = db_get_current_event_id();
        echo "var event_id = $event_id;";
    ?>
    
    update_request = $.getJSON("gettweets.php", "", 
    function(data, status, xhr){
        for (var i = 0; i< data.length; ++i)
        {
            var container = $("#tweets")[0];
            
            var curr_tweet = data[i];
            var author = curr_tweet[1];
            var tweet_text = curr_tweet[0];
            var img_link = curr_tweet[2];
            var dummy_id = curr_tweet[3];
            
            var tweet = new Tweet(tweet_text,author,dummy_id);
            tweets[i] = tweet;
            
            // each element is a tweet object       
            var tweet_div = document.createElement("div");
            
            var posted_by = document.createElement("span");
            posted_by.appendChild(document.createTextNode(author));
            $(posted_by).addClass("postedby");
            
            var content = document.createElement("span");
            content.appendChild(document.createTextNode(tweet_text));
            $(content).addClass("content");
            
            // add a clicked field to interact with server
            var linkElem = document.createElement('a');
            $(linkElem).addClass("add_link");
            linkElem.href="#like";
            linkElem.appendChild(document.createTextNode("Add this"));
            
            
            var clickMethod = function(number){
                return function(event){
                    event.preventDefault();
                    $.post("add_tweets.php", "post=" + tweets[number].text + "&event_id=" + event_id +"&dummy_id="+tweets[number].dummy_id, function(data, status, xhr) { 

                    });
                    return false;
                }
            }

            $(linkElem).click(clickMethod(i));

            tweet_div.appendChild(posted_by);
            tweet_div.appendChild(content);
            tweet_div.appendChild(linkElem);
            
            if (container == null){
                $("#tweets").html(tweet_div);
            }
            else if (container.childNodes.length == 0) {
                container.appendChild(tweet_div);
            } else {
                container.insertBefore(tweet_div, container.childNodes[0]);
            }
        }
    });
    
    </script>
</head>

<body>
<div id="tweets">

</div>

</body>    

</html>