<?php
require("common.php");
db_connect(/*is_connection_required*/true);
require_superuser_access();

?>


<!DOCTYPE html>
<html>
<head>
    <title>quobit post monitoring</title>
    
    <style>
    .content 
    {
        margin-left:3em;
    }
    .remove_link 
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
    
    <?php
        $event_id = db_get_current_event_id();
        echo "var event_id = $event_id;";
    ?>
    
    function MiniPost(id, author, message) {
      this.id = id;
      this.message = message;
      this.author = author;
    }
    
    var posts = {};
    var posts_removed = {};
    
    $.getJSON("posts.php", "last_post_id=0&last_update_id=0&reply_post_id=0&last_reply_id=0&event_id=" + event_id +"&last_reply_update_id=0", 
        function(data, status, xhr) {
          var all_posts = data[0];

          for (var i = 0; i< all_posts.length; ++i)
          {
          
            var container = $("#posts")[0];
            
            var curr_post = all_posts[i];
            var post_message = curr_post[0];
            var post_author = curr_post[1];
            var post_id = curr_post[3];
            var to_remove = curr_post[10];
            
            if (to_remove == 1){
                continue;
            }

            var minipost = new MiniPost(post_id,post_author,post_message);
            posts[i] = minipost;
            
            // each element is a tweet object       
            var post_div = document.createElement("div");
            
            var posted_by = document.createElement("span");
            posted_by.appendChild(document.createTextNode(post_author));
            $(posted_by).addClass("postedby");
            
            var content = document.createElement("span");
            content.appendChild(document.createTextNode(post_message));
            $(content).addClass("content");
            
            // add a clicked field to interact with server
            var linkElem = document.createElement('a');
            $(linkElem).addClass("remove_link");
            linkElem.href="#remove";
            linkElem.appendChild(document.createTextNode("Remove this post"));
            
            
            var clickMethod = function(number){
                return function(event){
                    event.preventDefault();
                    $.post("remove_post.php", "id=" + posts[number].id + "&to_remove=1", function(data, status, xhr) { 

                    });
                    return false;
                }
            }

            $(linkElem).click(clickMethod(i));

            post_div.appendChild(posted_by);
            post_div.appendChild(content);
            post_div.appendChild(linkElem);
            
            if (container == null){
                $("#posts").html(post_div);
            }
            else if (container.childNodes.length == 0) {
                container.appendChild(post_div);
            } else {
                container.insertBefore(post_div, container.childNodes[0]);
            }
        }
        }
    );
    
    
        $.getJSON("posts.php", "last_post_id=0&last_update_id=0&reply_post_id=0&last_reply_id=0&event_id=" + event_id +"&last_reply_update_id=0", 
        function(data, status, xhr) {
          var all_posts = data[0];

          for (var i = 0; i< all_posts.length; ++i)
          {
          
            var container = $("#removed_posts")[0];
            
            var curr_post = all_posts[i];
            var post_message = curr_post[0];
            var post_author = curr_post[1];
            var post_id = curr_post[3];
            var to_remove = curr_post[10];
            
            if (to_remove == 0){
                continue;
            }

            var minipost = new MiniPost(post_id,post_author,post_message);
            posts_removed[i] = minipost;
            
            // each element is a tweet object       
            var post_div = document.createElement("div");
            
            var posted_by = document.createElement("span");
            posted_by.appendChild(document.createTextNode(post_author));
            $(posted_by).addClass("postedby");
            
            var content = document.createElement("span");
            content.appendChild(document.createTextNode(post_message));
            $(content).addClass("content");
            
            // add a clicked field to interact with server
            var linkElem = document.createElement('a');
            $(linkElem).addClass("remove_link");
            linkElem.href="#remove";
            linkElem.appendChild(document.createTextNode("Unblock this post"));
            
            
            var clickMethod = function(number){
                return function(event){
                    event.preventDefault();
                    $.post("remove_post.php", "id=" + posts_removed[number].id +"&to_remove=0", function(data, status, xhr) { 
                    });
                    return false;
                }
            }

            $(linkElem).click(clickMethod(i));

            post_div.appendChild(posted_by);
            post_div.appendChild(content);
            post_div.appendChild(linkElem);
            
            if (container == null){
                $("#removed_posts").html(post_div);
            }
            else if (container.childNodes.length == 0) {
                container.appendChild(post_div);
            } else {
                container.insertBefore(post_div, container.childNodes[0]);
            }
        }
        }
    );
    </script>
</head>

<body>
Posts:
<div id="posts">

</div>

<div id="hrule"> ------------------------- </div>
<div id="bottom_header"> Removed Posts:
</div>
<div id="removed_posts">

</div>

</body>    

</html>