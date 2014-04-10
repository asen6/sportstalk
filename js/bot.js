// bot.js
// additional file to simulate user activity

// Page ready handler.
function init() {

  // set up loop to start posting and replying
  randomposts();
  randomreplies();
}
$(document).ready(init);


function randomposts()
{
var random_post = randstr(15);
makepost(random_post);
t=setTimeout("randomposts()",60000);
}

function randomreplies()
{
  // pick a post to reply to
  var random_post_id = post_ids[(Math.floor(Math.random()*post_ids.length))];
  var random_string = randstr(10);

  reply_to_post(random_post_id, random_string);
  t=setTimeout("randomreplies()",10000);
}

function makepost(post_text)
{
  post_text = escape(post_text);
  $.post("add_post.php", "post=" + post_text, function(data, status, xhr) {
    request_update();
  }, "json");
  
  $("#comment")[0].value = "";
}

function reply_to_post(post_id,reply_text)
{
  reply_text = escape(reply_text);

  $.post("add_reply.php", "post=" + reply_text + "&post_id=" + post_id, function(data, status, xhr) {
    request_update();
  }, "json");
  $("#reply_text")[0].value = "";
}

// library type function
function randstr(len)
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < len; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}
