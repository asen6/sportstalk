// post.js

function Post(post_id, author, message, post_time, reply_count, liked, author_fbid, author_id, like_count, to_remove) {
  this.post_id = post_id;
  this.author = author;
  this.author_fbid = author_fbid;
  this.author_id = author_id;
  this.message = message;
  this.post_time = post_time;
  this.reply_count = reply_count;
  this.new_replies = 0;
  this.is_updated = false;
  this.div = null;
  this.liked = liked;
  this.last_update_time = current_time; // set last update time to page load time
  this.like_count = like_count;
  this.replies = [new Reply(-1, post_id, author, message)];
  this.last_update_reply_id = 0;
  this.to_remove = to_remove;
  this.replies_hash = {};
}

Post.prototype.update_reply_count = function(new_count, current_chat_id) {

  if (this.post_id == current_chat_id){
    this.reply_count = new_count;
    this.new_replies = 0;
  }  
  else {
    this.new_replies = new_count - this.reply_count;
    if (new_count > this.reply_count) {
        this.is_updated = true;
    }
  }
}

Post.prototype.add_replies = function(new_replies) {  
  // Verify that the replies are all for this post.
  if (window.DEBUG) {
    var last_reply_id = -1;
    for (var i = 0; i < new_replies.length; ++i) {
      DEBUG.assert(new_replies[i].post_id == this.post_id, "Replies given to Post.add_replies must match the post id.");
    }
  }
  
  for (var i = 0; i < new_replies.length; ++i) {
    if (!(new_replies[i].reply_id in this.replies_hash)) {
      this.replies_hash[new_replies[i].reply_id] = true;
      this.replies.push(new_replies[i]);
      this.is_updated = true;
    }
  }
}

Post.prototype.get_last_reply_id = function() {
  if (this.replies.length == 0) {
    return 0;
  } else {
    return this.replies[this.replies.length - 1].reply_id;
  }
}

function Reply(reply_id, post_id, author, message, liked, author_fbid, author_id, like_count) {
  this.reply_id = reply_id;
  this.post_id = post_id;
  this.author = author;
  this.author_fbid = author_fbid;
  this.author_id = author_id;
  this.message = message;
  this.liked = liked;
  this.like_count = like_count;
  this.realized = false;
  this.div = null;
}