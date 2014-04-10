// index.js

// Constants.
var INITIAL_TIMEOUT = 500;
var POLL_TIMEOUT = 1000;

// Page state.
var posts = {};
var post_ids = [];
var update_timeout = null;
var update_request = null;
var current_chat_id = -1;
var last_update_id = 0;
var last_server_post_id = -1; // tells us the id's to look at for posts.php call
var last_reply_update_id = 0;
var current_time = -1;
var is_banned = false;

// Help page state
var help_page_index = 0;

// user state
var autoscroll = true;

// time formatting array
var phrase = ['minute', 'hour', 'day', 'week', 'month', 'year', 'decade'];
var timelength = [60, 3600, 86400, 604800, 2630880, 31570560, 315705600];

// May be set in index.php.
var user_id = null;
var invitation = null;
var event_id = null;
var event_name = null;
var facebook_id = null;
var username = null;

var collapse_seconds = 50;

// variables for dealing with new post notifications
var newpostcount = 0;
var blurred = false;

// variables for dealing with rebuilding the chat area
var rebuild_chat = false;

// Page ready handler.
function init() {

    $(window).bind('focus', function() {                
    if (blurred) {
        document.title = "quobit"; // reset the title
        blurred = false;
        newpostcount = 0;
    }
    });

    $(window).bind('blur', function() {
        if (!blurred) {
            blurred = true;    
        }
    } );

  // Set up event handlers.
  $("#login_button").click(function() { login($("#st_username")[0].value,$("#password")[0].value); });
  $("#signup_button").click(function() { window.location="register.php";});
  $("#logout_button").click(logout);
  $("#post").click(post_clicked);
  $("#reply_button").click(reply_clicked);
  $("#request_invite").click(email_submit);
  $("#help_container").click(function() { hide_help_overlay(true); });
  $("#leaderboard_link").click(toggle_leaderboard);

        
  // Download the initial posts.
  setTimeout(function() { request_update(true, initial_callback) }, INITIAL_TIMEOUT);
    
  // check for cookie to determine username
  if (user_id == null || user_id == -1) {
    show_overlay(/*animate_transition*/true);
  } else {
    hide_overlay(/*animate_transition*/false);
  }

  // initialize these counts so that before first update, there's something to display
  $("#point_count").text(0);
  $("#post_cost").text(0);
  
  bind_to_enter("#comment", "#post");
  bind_to_enter("#reply_text", "#reply_button");
  bind_to_enter("#email", "#request_invite");
  
  $.ajaxSetup({"error":function(xhr,textStatus, errorThrown) {   
    // If we get an ajax error, renew the update.
    if (xhr.status != 0) {
      setTimeout(function() { request_update(true) }, POLL_TIMEOUT);
    }
  }});
}
$(document).ready(init);

function initial_callback() {
  if (post_ids.length > 0) {
      set_current_post(post_ids[post_ids.length - 1]);
      return true;
  } else {
    setTimeout(function() {
        request_update(true, initial_callback);
      },
      POLL_TIMEOUT
    );
    return false;
  }
}

function show_overlay(animate_transition) {
  //comment when want to go to pre-launch mode
  if (animate_transition) {
    $("#login_container").fadeIn();
  } else {
    $("#login_container").show();
  }
  $("#st_username").select();
  
  // uncomment when want to go to pre-launch mode
  //$("#prelaunch_splash_container").show();
}

function hide_overlay(animate_transition) {
  if (animate_transition) {
    $("#login_container").fadeOut();
  } else {
    $("#login_container").hide();
  }
  $("#comment_box").select();
}

function show_help_overlay(animate_transition) {
  if (animate_transition) {
    $("#help_container").fadeIn();
  } else {
    $("#help_container").show();
  }
}

function hide_help_overlay(animate_transition) {
  if (animate_transition) {
    $("#help_container").fadeOut();
  } else {
    $("#help_container").hide();
  }
  $("#comment_box").select();
}

// Takes two jquery selectors for text fields and buttons and maps the
// enter key for those fields to click the buttons.
function bind_to_enter(text, button) {
  $(text).keypress(function(e) {
      if (e.which == 13) {
        $(button)[0].click();
        return false;
      }
    });
}

function request_update(interrupt, callback) {
  if (event_id == -1 || event_id == null) {
    // TODO: cheesy ui
    alert("The event has ended.  Visit us again during our next event!");
    window.location.href = "home.php";
  }

  if (update_request && interrupt) {
    update_request.abort();
    update_request = null;
  }
  
  if (update_timeout) {
    window.clearTimeout(update_timeout);
    update_timeout = null;
  }
  
  get_updates(callback);
}

// function to clear the update_timeout
function abort_update(){
    window.clearTimeout(update_timeout);
    update_timeout = null;
}

function set_event_and_request_update(new_event_id) {
  // Tear down the posts and request an update.
  $("#msgs").html("");
  $("#replies").html("");
  event_id = new_event_id;
  posts = {};
  post_ids = [];
  current_chat_id = -1;
  last_update_id = 0;
  last_reply_update_id = 0;
  current_time = -1;
  request_update(true, initial_callback);
}

function last_post_id() {
  if (last_server_post_id > 0) {
    return last_server_post_id;
  } else {
    return 0;
  }
}

// TO DO: change to incorporate skipping own added posts
function get_updates(callback) {
  var last_reply_id = 0;
  if (current_chat_id != -1) {
    last_reply_id = posts[current_chat_id].last_update_reply_id;
  }
  update_request = $.getJSON("posts.php", "last_post_id=" + last_post_id() + "&last_update_id=" + last_update_id + "&reply_post_id=" + current_chat_id + "&last_reply_id=" + last_reply_id + "&event_id=" + event_id +"&last_reply_update_id=" + last_reply_update_id, 
    function(data, status, xhr) {
      var new_posts = data[0];
      var new_updates = data[1];
      var new_replies = data[2];
      var unix_time = data[3];
      var user_points = data[4];
      var post_threshold = data[5];
      var active_users = data[6];    
      var new_reply_updates = data[7];
      var current_event_id = data[8];
      
      if (data[9] != is_banned) {
        // The user was either just banned or unbanned.
        is_banned = data[9];
        if (!is_banned) {
          alert("Hooray! Your ban was lifted and you may now contribute again.");
        } else {
          alert("Oops! Your account has been temporarily banned and you may no longer contribute.  Please contact banned@quobit.com to have your ban lifted.");
        }
      }
      
      current_time = unix_time;
      
      if (current_event_id != event_id) {
        set_event_and_request_update(current_event_id);
        return;
      }
    
      var modified_points = Math.floor(user_points/100);
      var modified_threshold = Math.ceil(post_threshold/100);
    
      // update point message
      if (modified_points < modified_threshold) {
        document.getElementById("post").style.backgroundColor = "#ffffff";
        document.getElementById("post").style.borderColor = "#ccc";
        document.getElementById("post").style.color = "#ccc";
        $('#post').attr('disabled', 'disabled');
      }
      else {
        document.getElementById("post").style.backgroundColor = "#FFDF70";
        document.getElementById("post").style.borderColor = "#bfa700";
        document.getElementById("post").style.color = "#333";
        $('#post').attr('disabled', '');
      }
      //$("#point_indicator").text("You have " + modified_points + " points. Posts cost " + modified_threshold + " points.");
      $("#point_count").text(modified_points);
      $("#post_cost").text(modified_threshold);
    
      var userstext = "";
      if (active_users == 1)
      {
          userstext = "1 active user";
      }
      else 
      {
          userstext = active_users + " active users";
      }
      $("#active_users").text(userstext);
    
      // Add any new posts.
      for (var i = 0; i < new_posts.length; ++i) {
        var post_array = new_posts[i];
        var post_id = post_array[3];
        var post_message = post_array[0];
        var post_author = post_array[1];
        var post_time = post_array[2];
        var post_reply_count = post_array[4];
        var post_last_reply = post_array[5];
        var liked = post_array[6];
        var author_fbid = post_array[7];
        var author_id = post_array[8];
        var like_count = post_array[9];
        var to_remove = post_array[10];
        
        // if post id doesn't exist, add the new post
        if (!posts[post_id]) {
          var post = new Post(post_id, post_author, post_message, post_time, post_reply_count, liked, author_fbid, author_id, like_count, to_remove);
          posts[post_id] = post;
          post_ids.push(post_id); // now post_ids reflects the order to look at the posts in  
          
          if (blurred){
            ++newpostcount; // increment the new post count
          }
        }
        else { // a post by current user, might want to update details (post_ids array should already contain this)     
        }
        
        if (blurred)
        {
            document.title = "quobit (" + newpostcount + ")";
        }
        
        if (post_id > last_server_post_id)
        {
            last_server_post_id = post_id; // update count to be used in later update calls
        }
      }
    
      // Look at the updates.
      for (var i = 0; i < new_updates.length; ++i) {
        var update = new_updates[i];
        var update_id = update[0];
        var post_id = update[1];
        var reply_count = update[2];
        var like_count = update[4];
        var to_remove = update[5];
      
        if (update_id > last_update_id) {
          last_update_id = update_id;
        
          if (posts[post_id]) {
            posts[post_id].like_count = like_count;
            posts[post_id].last_update_time = current_time; // used for determining collapsing of posts
            if ($(posts[post_id].div).hasClass("collapse"))
            {
                $(posts[post_id].div).removeClass("collapse");
            }
            posts[post_id].update_reply_count(reply_count, current_chat_id);
            
            posts[post_id].to_remove = to_remove;
            
          } else if (window.DEBUG) {
            DEBUG.assert(false, "Updates should refer to posts that have already been retrieved.");
          }
        }
      }
    
      // Add any new replies.
      var new_replies_objects = []
      for (var i = 0; i < new_replies.length; ++i) {
        var reply = new_replies[i];
        var reply_id = reply[0];
        var reply_post_id = reply[1];
        var reply_author = reply[2];
        var reply_message = reply[3]; 
        var liked = reply[4];
        var author_fbid = reply[5];
        var author_id = reply[6];
        var like_count = reply[7];
        
        // check if this reply_id already exists
        var alreadythere = false;
        
        // remnant of update during post and switch
        if (reply_post_id != current_chat_id){
            continue;
        }
        
        for (var j = 0; j < posts[reply_post_id].replies.length; ++j)
        {
            if (posts[reply_post_id].replies[j].reply_id == reply_id)
            {
                alreadythere = true;
                break;
            }
        }
        
        // add if this is a new reply
        if (!alreadythere){
            new_replies_objects.push(new Reply(reply_id, reply_post_id, reply_author, reply_message, liked, author_fbid, author_id, like_count));
        }
        if (reply_id > posts[current_chat_id].last_update_reply_id)
        {
            posts[current_chat_id].last_update_reply_id = reply_id;
        }
      }
    
      if (new_replies_objects.length > 0) {
        var post_id = new_replies_objects[0].post_id;
        if (posts[post_id]) {
          posts[post_id].add_replies(new_replies_objects);
        } else if (window.DEBUG) {
          DEBUG.assert(false, "Replies should refer to posts that have already been received.");
        }
      }

      // update using any reply updates if current post id is set
      if (posts[current_chat_id]){
          var replyarray = posts[current_chat_id].replies;
          for (var i = 0; i < new_reply_updates.length; ++i)
          {
            // reply_updates.id, replies.id, replies.like_count, replies.post_id 
            var reply_update_obj = new_reply_updates[i];
            var reply_update_id = reply_update_obj[0];
            var reply_id = reply_update_obj[1];
            var reply_likes = reply_update_obj[2];
            var reply_post_id = reply_update_obj[3];
            
            // loop through to update the correct reply object in current post
            for (var j = 0; j < replyarray.length; ++j)
            {
                if (reply_id == replyarray[j].reply_id)
                {
                    replyarray[j].like_count = reply_likes;
                    posts[current_chat_id].is_updated = true; // trigger rebuild
                }
            }
          }
      }
      
      
      // We might have changed some data, so trigger an update.
      posts_changed();
    
      // Fire the callback if there is one.
      var continue_updating = true;
      if (callback) {
        continue_updating = callback();
      }
    
      // Set a timeout for the next update.
      if (continue_updating) {
        if (update_timeout) {
          if (window.DEBUG) {
            //DEBUG.assert(false, "Got multiple timeouts set.");
          }
          window.clearTimeout(update_timeout);
          update_timeout = null;
        }
    
        update_request = null;
        update_timeout = window.setTimeout(function() { request_update(); }, POLL_TIMEOUT);
      }
  });
}

function posts_changed() {  
  // Loop over the posts, looking for any posts that don't have divs, or ones that are marked updated.
  var current_post_changed = false;
  
  var container = $("#msgs")[0];
  
  // loop through all posts here
  for (var i = 0; i < post_ids.length; ++i) {
    var post_id = post_ids[i];
    var post = posts[post_id];
    if (post) {
    
      // if marked for removal, make div hidden
      if (post.to_remove == 1)
      {
        $(post.div).hide();
      }
      else {
        $(post.div).show();
      }      
    
      // update collapsing here based on data (no new replies to see, no updates for "collapse_seconds" seconds and not in top five)
      if (!post.is_updated && post.post_id != current_chat_id && i < post_ids.length - 5 && current_time - post.last_update_time > collapse_seconds)
      {
        $(post.div).addClass("collapse");
      }
    
      if (post.div == null) {
        // Create a div.
        post.div = post_to_dom(post);
        
        // animate the posting?
        
        if (container.childNodes.length == 0) {
          container.appendChild(post.div);
        } else {
          container.insertBefore(post.div, container.childNodes[0]);
          $(post.div).hide().fadeIn(300);
        }
      } else {
        if (post.is_updated && post.post_id == current_chat_id) {
          // If this is the current post, it doesn't need to be marked updated any longer.
          post.is_updated = false;
          current_post_changed = true;
        }
        
        update_post_div(post);
      }      
      
    } else if (window.DEBUG) {
      DEBUG.assert(false, "post_ids contained an id that posts didn't.");
    }
  }
  
  // Rebuild the replies for the current post.
  if (current_chat_id != -1 && current_post_changed) {
  
    // avoid rebuilding the whole chat (only add on the stuff not currently displayed)   
    var container = $("#replies")[0];
    var replies = posts[current_chat_id].replies;
    
    // builds the replies
    if (rebuild_chat){
        $("#replies").empty();
        for (var i=0; i < replies.length; i++) {
          replies[i].div = reply_to_dom(replies[i]);
          container.appendChild(replies[i].div);
          replies[i].realized = true;
        };
        rebuild_chat = false; // set to true again by a post change
    }
    else {
        for (var i=0; i < replies.length; i++) {
          if (!replies[i].realized){  
            replies[i].div = reply_to_dom(replies[i]);
            container.appendChild(replies[i].div);
            replies[i].realized = true;
          }
          else {
            if (replies[i].reply_id != -1){
                // update like counts of anything
                update_reply_div(replies[i]);
            }
          }
        };
    }
  
    // If autoscroll, we move to the new bottom
    var scroller = document.getElementById("replies_scroller");
    var height = scroller.clientHeight;
    var scroll = scroller.scrollHeight;
    var position = scroller.scrollTop; 

    // right now have a tolerance. If close enough to the bottom, will move you back
    // issue is with this getting called right after a new chat is loaded in
    // in reality, just want to check for position + height == scroll
    if (position + height + 150 >= scroll) {
      scroller.scrollTop = scroller.scrollHeight;
    }
  }
}

// Switches between the login, feed, and chat panes.
function show_pane(pane, fadeIn) {
  $(".pane").each(function(e, obj) { obj.style.display = "none"; });
  $(panes[feed_pane][0]).fadeIn(fadeIn ? 200 : 0);
  if (pane == 0) {
     $(panes[pane][0]).fadeIn(fadeIn ? 200 : 0);
  }
  var field = $("#" + panes[pane][1]);
  field.select();
}

// Event handler for clicking the post button.
function post_clicked() {
  var comment = escape($("#comment")[0].value);
  var raw_comment = $("#comment")[0].value;
  
  var curr_post_id = -1; // usable in outside scope
  var curr_position = -1;
  
  // can get around this, but will only see own posts
  if (!is_banned){  
      // freeze updating
      abort_update();
  

      
      // need this operation to finish before allowing another update
      $.post("add_post.php", "post=" + comment + "&event_id=" + event_id, function(data, status, xhr) { 
        // change id of the post added above to the one given to it by the server
        if (data[0] >= 0){   
            var actual_post_id = data[0];
            
            var post = new Post(actual_post_id, username, raw_comment, get_unixtime_js(), 0, false, facebook_id, user_id, 0, 0);
            posts[actual_post_id] = post;
            post_ids.push(actual_post_id);
            
            posts_changed();
            $("#comment")[0].value = ""; // only clear if the post went through ok
        }
        else {
        
        }
               
        update_request = null;
        update_timeout = window.setTimeout(function() { request_update(); }, POLL_TIMEOUT);
      }, "json");
      
      
  }
  

}

// Event handler for clicking the reply button.
function reply_clicked() {
  var post_id_here = current_chat_id; // avoid problems in current_chat_id changes through the process (clicking a new post)
            
  var comment = escape($("#reply_text")[0].value);
  var raw_comment = $("#reply_text")[0].value;
  
  // let something happen if there is a current chat id
  if (current_chat_id != -1){
  
      // can get around this, but will only see own reply
      var curr_post_id = -1;
      
      if (!is_banned){
      
        // freeze updating
        abort_update();

        $.post("add_reply.php", "post=" + comment + "&post_id=" + post_id_here, function(data, status, xhr) {
            
            // Add the reply to post.
            var assigned_id = data[0];

            var new_replies_objects = [];
            new_replies_objects.push(new Reply(assigned_id, post_id_here, username, raw_comment, 0, facebook_id, user_id, 0));

            posts[post_id_here].add_replies(new_replies_objects);
            ++posts[post_id_here].reply_count; // don't rely only on update from server to determine reply count
            posts_changed();      
                  
            update_request = null;
            update_timeout = window.setTimeout(function() { request_update(); }, POLL_TIMEOUT);

        }, "json");
        $("#reply_text")[0].value = "";
      }
  }
}

function update_post_div(post) {
  $("#" + post.div.id + " .timestamp").text(ShowDate(post.post_time));
  
  // update the like count
  if (post.author_id != user_id){
    if (post.liked){
        $("#" +post.div.id + " .postlike label").text("Liked (" + post.like_count.toString() + ")");
    }
    else {
        $("#" +post.div.id + " .postlike a").text("Like (" + post.like_count.toString() + ")");
    }
  }
  else {
    $("#" +post.div.id + " .postlike label").text("Likes: " + post.like_count.toString());
  }
  
  if (post.is_updated) {
    var replies = $("#" + post.div.id + " .replycount")[0];
    var new_replies = post.new_replies;
    if (new_replies == 1) {
      $(replies).text("1 new reply"); 
    } else {
      $(replies).text(new_replies.toString() + " new replies");
    }
    $(post.div).addClass("updated");
  } else {
    $(post.div).removeClass("updated");
  }
}

function update_reply_div(chat_to_update){

    // update the like count
    if (chat_to_update.author_id != user_id){
        if (chat_to_update.liked == 1){
            $("#" +chat_to_update.div.id + " .replylike label").text("Liked (" + chat_to_update.like_count.toString() + ")");
        }
        else {
            $("#" +chat_to_update.div.id + " .replylike a").text("Like (" + chat_to_update.like_count.toString() + ")");
        }
    }
    else {
        $("#" +chat_to_update.div.id + " .replylike label").text("Likes: " + chat_to_update.like_count.toString());
    }
}


// Takes a post and builds a div that represents it in the feed.
function post_to_dom(post) {
  var msg = document.createElement("div");
  msg.id = "post_" + post.post_id;
  $(msg).addClass("message");
  
  var content = document.createElement("div");
  $(content).addClass("content");
  $(content).html(detect_links(post.message));
  
  // text below content in post bubble
  var bottom_text = document.createElement("div");
  $(bottom_text).addClass("bottomtext");
  
  var posted_by = document.createElement("span");
  posted_by.appendChild(document.createTextNode(post.author));
  $(posted_by).addClass("postedby");
  
  var timestamp = document.createElement("span");
  $(timestamp).addClass("timestamp");
  timestamp.appendChild(document.createTextNode(ShowDate(post.post_time)));
  
  
  bottom_text.appendChild(posted_by);
  bottom_text.appendChild(timestamp);
  
  // add an element representing the like button
  var likepart = document.createElement("span");
  $(likepart).addClass("postlike");
  
  if (post.author_id != user_id) {
    if (post.liked == 0) { // not liked, display clickable link
      var linkElem = document.createElement('a');
      linkElem.href="#like";
      $(linkElem).click(
        function(event) {
          event.preventDefault();
      
          if (post.author_id != user_id) {
            $.getJSON("addpostlike.php", "post_id=" + post.post_id.toString(), function(data, status, xhr) {});
            $(likepart).empty();
            var newPostlikeCount = post.like_count + 1;
            post.like_count = newPostlikeCount;
            var labelElem = document.createElement('label');
            var labelElemTN = document.createTextNode("Liked (" + newPostlikeCount.toString() + ")");
            labelElem.appendChild(labelElemTN);
            likepart.appendChild(labelElem);
            post.liked = 1;
          }
          return false;
      });

      var linkElemTN = document.createTextNode("Like (" + post.like_count.toString() + ")");
      linkElem.appendChild(linkElemTN);
      likepart.appendChild(linkElem);
    } else {
      // already liked
      var labelElem = document.createElement('label');
      var labelElemTN = document.createTextNode("Liked (" + post.like_count.toString() + ")");
      labelElem.appendChild(labelElemTN);
      likepart.appendChild(labelElem);
    }
    bottom_text.appendChild(likepart);
  }
  else // show the author the like count 
  {
    var labelElem = document.createElement('label');
    var labelElemTN = document.createTextNode("Likes: " + post.like_count.toString());
    labelElem.appendChild(labelElemTN);
    likepart.appendChild(labelElem);
    bottom_text.appendChild(likepart);
  }
  
  var replies = document.createElement("div");
  $(replies).addClass("replycount");
  
  if (post.is_updated){
    var new_replies = post.new_replies;
    if (new_replies == 1)
    {
        $(replies).text("1 new reply"); 
    }
    else {
        $(replies).text(new_replies.toString() + " new replies");
    }
    $(msg).addClass("updated");
  }
  
  // add profile picture
  if (post.author_fbid != "-1") { // have it link to facebook page of the author
    var profpic = document.createElement("div");
    $(profpic).addClass("profile_picture");
    
    var profile_link = document.createElement('a');
    profile_link.href = "http://www.facebook.com/profile.php?id=" + post.author_fbid;
    
    profile_link.target= "_blank";
    
    var img = document.createElement('img');
    img.src = "http://graph.facebook.com/" + post.author_fbid + "/picture?type=square";
    profile_link.appendChild(img);
    
    profpic.appendChild(profile_link);
    
    var left = document.createElement("span");
    $(left).addClass("msg_left");
    left.appendChild(profpic);
    msg.appendChild(left);
  }
  else { // it's a twitter post
    var profpic = document.createElement("div");
    $(profpic).addClass("profile_picture");
    var img = document.createElement('img');
    img.src = "twitter.png";
    profpic.appendChild(img);
    
    var left = document.createElement("span");
    $(left).addClass("msg_left");
    left.appendChild(profpic);
    msg.appendChild(left);
  }
  
  msg.appendChild(replies);
  msg.appendChild(content);
  msg.appendChild(bottom_text);
  
  var spacer = document.createElement("div");
  $(spacer).addClass("spacer");
  msg.appendChild(spacer);
  
  $(msg).mousedown(function() {
    set_current_post(post.post_id);
    $("#reply_text")[0].focus();
    return false;
  });
  
  if (current_chat_id == post.post_id) {
    $(msg).addClass("selected");
  }
 
  return msg;
}

// post changed
function set_current_post(post_id) {
  var post = posts[post_id];
  post.is_updated = false;
  post.new_replies = 0;
  
  if (post_id == current_chat_id) {
    // Nothing to do.
    return;
  }
  
  // uncollapse post if necessary
    if ($(post.div).hasClass("collapse"))
    {
        $(post.div).removeClass("collapse");
    }
  // update the time of the last update for previous selected post 
  // so that there isn't an immediate collapse
  if (current_chat_id > 0)
  {
    posts[current_chat_id].last_update_time = Math.round((new Date()).getTime() / 1000);
  }
  
  $(".selected").removeClass("selected");
  $(post.div).addClass("selected");
  $(post.div).removeClass("updated");
  
  post.is_updated = true;
  rebuild_chat = true; // tell updater to rebuild the chat
  
  current_chat_id = post_id;
  posts_changed();
  $("#replies_scroller")[0].scrollTop = $("#replies_scroller")[0].scrollHeight;
  
  request_update(true, function() {
      $("#replies_scroller")[0].scrollTop = $("#replies_scroller")[0].scrollHeight;
      return true;
    });
}

function reply_to_dom(chat) {
  var row = document.createElement("div");
  row.id = "reply_" + chat.reply_id;
  $(row).addClass("message");

  var posted_by = document.createElement("span");
  $(posted_by).addClass("postedby");
  
  var author_name = document.createTextNode(chat.author);
  posted_by.appendChild(author_name);
  

  var content = document.createElement("span");
  $(content).addClass("content");
  $(content).html(detect_links(chat.message));
  
  // find youtube id if necessary
  var yid = detect_first_youtube_link_id(chat.message);
  
  // append some element to the end of content
  var youtubediv = document.createElement("div");
  $(youtubediv).addClass("youtube");
  
  if (yid != ""){
      var thumbnail_link = document.createElement("a");
      thumbnail_link.href = "#video";
      
      $(thumbnail_link).click(
          function(event) {
            event.preventDefault();
            load_video(yid, youtubediv);
          }
      );
      
      var img = document.createElement("img");
      img.src = "http://img.youtube.com/vi/" + yid + "/0.jpg";
      img.height = 100;
      img.width = 160;
      thumbnail_link.appendChild(img);
      youtubediv.appendChild(thumbnail_link);
      
      // add a hide link
      var hide_link = document.createElement("a");
      var hide_link_text = document.createTextNode("Hide Video");
      hide_link.href = "#hide";
      
      $(hide_link).click(
        function(event){
            event.preventDefault();
            hide_video(youtubediv, hide_link);
        }
      );
      
      hide_link.appendChild(hide_link_text);
      
      var space_text = document.createTextNode(" ");
      content.appendChild(space_text);
      content.appendChild(hide_link);
  }
  
  if (chat.reply_id > 0) {
    // add an element representing the like button
    var likepart = document.createElement("span");
    $(likepart).addClass("replylike");
    
    if (chat.author_id != user_id) {
      if (chat.liked == 0){
        var linkElem = document.createElement('a');
        linkElem.href="#like";
        $(linkElem).click(
          function(event) {
            event.preventDefault(); 
            $.getJSON("addreplylike.php", "reply_id=" + chat.reply_id.toString(), function(data, status, xhr) {});
    
            chat.liked = 1;
            $(likepart).empty();
            var newChatCount = chat.like_count + 1;
            chat.like_count = newChatCount;
            var labelElem = document.createElement('label');
            var labelElemTN = document.createTextNode("Liked (" + newChatCount.toString() + ")");
            labelElem.appendChild(labelElemTN);
            likepart.appendChild(labelElem);
            return false;
          }
        );

        var linkElemTN = document.createTextNode("Like (" + chat.like_count.toString() + ")");
        linkElem.appendChild(linkElemTN);
        likepart.appendChild(linkElem);
      } else {
        //likepart.appendChild(document.createTextNode("Liked"));
        var labelElem = document.createElement('label');
        var labelElemTN = document.createTextNode("Liked (" + chat.like_count.toString() + ")");
        labelElem.appendChild(labelElemTN);
        likepart.appendChild(labelElem);
      }
    }
    else {
        // display the like count to the replier
        var labelElem = document.createElement('label');
        var labelElemTN = document.createTextNode("Likes: " + chat.like_count.toString());
        labelElem.appendChild(labelElemTN);
        likepart.appendChild(labelElem);
    }
  }

  row.appendChild(posted_by);
  row.appendChild(content);
  if (chat.reply_id > 0){
    row.appendChild(likepart);
  }
  if (yid != ""){
    row.appendChild(youtubediv);
  }
  return row;
}

function close_help_handler(event){
    event.preventDefault(); 
}

function camonacho_login(access_token) {
    var invitation_argument = (invitation == null ? "" : "&invitation=" + invitation);
    $.getJSON("login.php", "accesstoken=" + access_token + invitation_argument, function(data, status, xhr) {
        if (data[0] > 0) {
            hide_overlay(/*animate_transition*/true);
            user_id = data[0];
            facebook_id = data[2];
            username = data[3];
            
            if (data[1] == 0) {
                show_help_overlay(1);
            }
        }
        else if (data[0] == -2) {
            $("#fb_form, #email_form").toggle();
            $("#email").focus();
        }
        else {
            $("#fb_error").text(data[1]);
        }
    });
}

// function for handling email
function email_submit() {
  var email = $("#email")[0].value;
  $.post("add_email.php", "email=" + email, function(data) {
    if (data[0] < 0) {
      alert(data[1]);
    } else {
      $("#email_form").text(data[1]);
    }
  }, "json");
}

function fblogin() {
  FB.getLoginStatus(function (r) {
    // Check if we need to log in to facebook or not.
    if (r.session) {
      $("#fb_error").text("Logging in...");
      var accesstoken = r.session['access_token'];
      camonacho_login(accesstoken);
    } else {
      
      FB.login(function(args) {
        $("#fb_error").text("Logging in...");
        var accesstoken = args['session']['access_token']; // may change if permissions change
        camonacho_login(accesstoken);
      });     
    }
  });
}

function checkfbloginstatus(){
    
    FB.getLoginStatus(function (r) {
    // Check if we need to log in to facebook or not.
    if (!r.session) {
        show_overlay(/*animate_transition*/true);
    }
  });
}

function fblogout() {
    FB.logout(function(args) { });
}

function logout() {
  $.getJSON("clear_cookie.php", {}, function(result, status, xhr) {});
  show_overlay(/*animate_transition*/true);
  $("#fb_error").text("Please login with your facebook account.");
}


// date formatting function
function ShowDate(unixtime) // $date -->  time(); value
{
  var a;
  if (current_time < 0) {
    a = new Date();
  } else {
    a = new Date(parseInt(current_time) * 1000);
  }
  var b = new Date(parseInt(unixtime) * 1000)
  var diff = Math.floor((a - b) / 1000) // get number of seconds difference

  // loop through the array to figure out where the difference fits in
  var i = 0;
  for (i = timelength.length - 1; diff < timelength[i] && i >= 0; i--) { }

  if (i < 0) {
    return "less than a " + phrase[0] + " ago";
  }

  var time = a - (diff % length[i]);
  var no = Math.floor(diff / timelength[i]);

  var extra = "";
  if (no != 1) {
    extra = "s";
  }

  return no.toString() + " " + phrase[i] + extra + " " + "ago";
}

function get_unixtime_js()
{
    var foo = new Date; // Generic JS date object
    var unixtime_ms = foo.getTime(); // Returns milliseconds since the epoch
    var unixtime = parseInt(unixtime_ms / 1000);
    return unixtime;
}

// Replaces hyperlinks in a string with <a href> tags.
function detect_links(text)
{
    var result = "";
    var p = /\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?������]))/i;
    
    while (true) {
        var matcharray = text.match(p);
        
        if (matcharray == null || matcharray.length == 0) {
            result = result + text;
            break;
        }
        
        result = result + RegExp.leftContext;
        
        var link = RegExp.$1;
        if (link.substr(0,7) == "http://" || link.substr(0,8) == "https://") {
            result = result + "<a href='" + link + "' target='_blank'>" + link + "</a>";
        } else {
            result = result + "<a href='" + "http://" + link + "' target='_blank'>" + link + "</a>";
        }
        
        text = RegExp.rightContext;
    }
    
    return result; 
}

// YOUTUBE SECTION

// might want to incorporate this into main detect links function
function detect_first_youtube_link_id(text)
{
    var p = /\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>???????]))/i;
    
    while (true) {
        var matcharray = text.match(p);
        
        if (matcharray == null || matcharray.length == 0) {
            break;
        }

        var link = RegExp.$1;
        var youtubeid = getyoutubeidfromlink(link);
        
        if (youtubeid != ""){
            return youtubeid;
        }
        
        text = RegExp.rightContext;
    } 
    return ""; 
}
function getyoutubeidfromlink(url)
{
    if (url.indexOf("youtube.com") == -1 && url.indexOf("youtu.be") == -1){
        return "";
    }
    
    var id = getParameter(url,"v");
    
    if (id == null || id == "") {
        return ""
    }
    else {
        return id;
    }
}
function load_video(yid, loaddiv){

    $(loaddiv).empty();
    
    var vidblock = document.createElement("object");
    vidblock.width = "425";
    vidblock.height = "350";
    vidblock.data= "http://www.youtube.com/v/" + yid;
    vidblock.type = "application/x-shockwave-flash";
    
    var paramblock = document.createElement("param");
    paramblock.name = "src";
    paramblock.value = "http://www.youtube.com/v/" + yid;
    
    vidblock.appendChild(paramblock);

    loaddiv.appendChild(vidblock);
}
function hide_video(video_div, hide_link){
    $(video_div).hide();
    $(hide_link).empty();
    var hide_link_text = document.createTextNode("Show Video");
    hide_link.href = "#show";
    
    $(hide_link).click( 
        function(event){
            event.preventDefault();
            show_video(video_div, hide_link);
        }
    );
    hide_link.appendChild(hide_link_text);
}
function show_video(video_div, hide_link){
    $(video_div).show();
    $(hide_link).empty();
    var hide_link_text = document.createTextNode("Hide Video");
    hide_link.href = "#hide";

    $(hide_link).click( 
        function(event){
            event.preventDefault();
            hide_video(video_div, hide_link);
        }
    );
    
    hide_link.appendChild(hide_link_text);
}

function getParameter(url, name) {
    var urlparts = url.split('?');
    if (urlparts.length > 1) {
        var parameters = urlparts[1].split('&');
        for (var i = 0; i < parameters.length; i++) {
            var paramparts = parameters[i].split('=');
            if (paramparts.length > 1 && unescape(paramparts[0]) == name) {
                return unescape(paramparts[1]);
            }
        }
    }
    return null;
}

var is_leaderboard_request_inflight = false;
var facebook_friends = null;
var leaderboard_chosen_index = 0;

function toggle_leaderboard() {
  if (is_leaderboard_request_inflight) {
    return false;
  }

  var leaderboard = $("#leaderboard")[0];
  
  if (leaderboard.style.display == "") {
    // it's not visible right now
    is_leaderboard_request_inflight = true;
    $.ajax('leaderboard.php?event_id=' + event_id, {
        complete: function(xhr, status) {
            is_leaderboard_request_inflight = false;
          },
        dataType: "json",
        success: function(data, status, xhr) {
          leaderboard.style.display = "block";
          
          // get friend stuff
          if (facebook_friends == null) {
            $(leaderboard).html("Loading...");
            FB.getLoginStatus(function (r) {
                if (r.session) {
                  // get the facebook friends on the app
                  FB.api({method: 'friends.getAppUsers'},
                      function(response) {
                          // Go through the leaderboard and find the friends.
                          facebook_friends = {};
                          if (facebook_id) {
                            // Add ourself.
                            facebook_friends[facebook_id] = true;
                          }
                          
                          for (var i = 0; i < response.length; ++i) {
                            facebook_friends[response[i]] = true;
                          }
                          fill_leaderboards(leaderboard, data);
                      }
                  );
                } else {
                  // else make them log in? For now, just show the all users table
                  fill_leaderboards(leaderboard, data);         
                }
            });
          } else {
            fill_leaderboards(leaderboard, data);
          }
        }
      }
    );
  } else if (leaderboard.style.display == "block") {
    // it's visible right now
    leaderboard.style.display = "";
  }
  
  return false;
}

function make_leaderboard_table(leaders) {
  var table = "<table>";

  for (var i = 0; i < leaders.length; ++i) {
    var user = leaders[i];
    
    table += "<tr" + (user[1] == facebook_id ? " class=\"me\"" : "") + "><td class='name'>" + user[2] + "</td><td class='points'>" + Math.floor(user[3]/100) + "</td></tr>";;
  }
  table += "</table>";
  return table;
}

function fill_leaderboards(element, all_leaders) {
  var friend_leaders = null;
  if (facebook_friends != null) {
    friend_leaders = all_leaders.filter(function(u) { return u[1] in facebook_friends; });
  }
  
  var all_table = make_leaderboard_table(all_leaders);
  var friends_table = null;
  
  if (friend_leaders) {
    // We want to show the friend leaders as well.
    friends_table = make_leaderboard_table(friend_leaders);
    
    // Make the togglers.
    var fragments = [{title: "All Users", html:all_table}, {title: "Friends", html: friends_table}];
    
    for (var i = 0; i < fragments.length; ++i) {
      var fragment = fragments[i];
      // Create the div to contain the html fragments.
      fragment.container = document.createElement("div");
      fragment.container.style.display = (i == leaderboard_chosen_index ? "block" : "none");
      $(fragment.container).html(fragment.html);
      
      // Create the link to toggle the fragments.
      fragment.text = document.createElement("span");
      fragment.text.style.display = (i == leaderboard_chosen_index ? "inline" : "none");
      $(fragment.text).text(fragment.title);
      
      fragment.link = document.createElement("a");
      fragment.link.style.display = (i == leaderboard_chosen_index ? "none" : "inline");
      fragment.link.href = "#" + fragment.title;
      $(fragment.link).text(fragment.title);
      $(fragment.link).click((function(index) { return function() {
          leaderboard_chosen_index = index;
          for (var j = 0; j < fragments.length; ++j) {
            var fragment = fragments[j];
            if (j != index) {
              fragment.container.style.display = "none";
              fragment.text.style.display = "none";
              fragment.link.style.display = "inline";
            } else {
              fragment.container.style.display = "block";
              fragment.text.style.display = "inline";
              fragment.link.style.display = "none";
            }
          }
          return false;
        };})(i));
    }
    
    $(element).html("");
    
    // Append all the links/text.
    for (var i = 0 ; i < fragments.length; ++i) {
      $(element).append(fragments[i].link);
      $(element).append(fragments[i].text);
    }
    
    $(element).append(document.createElement("hr"));
    
    // Append all the containers.
    for (var i = 0; i < fragments.length; ++i) {
      $(element).append(fragments[i].container);
    }
  } else {
    $(element).html(all_table);
  }
}

function inarray(element, array){
    for (var i = 0; i < array.length; ++i)
    {
        if (element == array[i]){
            return true;
        }
    }
    return false;
}

// function called to show dialog box
function postToFeed() {
    // calling the API ...
    var obj = {
      method: 'feed',
      link: 'http://www.quobit.com/',
      picture: 'quobit.png',
      name: 'Quobit Live',
      caption: event_name,
      description: 'Discuss the game live with friends and fellow fans.' 
    };

    function callback(response) {
    }

    FB.ui(obj, callback);
}