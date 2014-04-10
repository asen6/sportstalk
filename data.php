<?php

// universal scaling (by 100 to fit with database)
define('UNIVERSAL_SCALE',100);

// Post cost equation: max(ax^2+b,c)
define('MAX_POST_COST',40);
define('POST_COST_B',0);
define('POST_COST_A',0.5);
define('POST_COST_WINDOW_MINUTES',1);
define('POST_RATE_THRESHOLD',6);

// user post cost equation
define('USER_MAX_POST_COST',20);
define('USER_POST_COST_B',0);
define('USER_POST_COST_A',1);
define('USER_POST_COST_WINDOW_MINUTES',1);
define('USER_POST_RATE_THRESHOLD',2);


// reply related constants
define('SWEETSPOT_SCALE',50); // sweetspot for replying (determines k(x) value, takes into account the 100 scaling)
define('REPLY_RATIO_SECONDS',30); // window in seconds to look at for scoring a reply
define('MAX_ORIGINAL_POSTER_POINTS',20);

// post related constants
define('MAX_POSTER_LIKE_POINTS',20);
define('MAX_POST_LIKE_POINTS',10);

// reply like
define('MAX_REPLY_AUTHOR_LIKE_POINTS',5);
define('MAX_REPLY_LIKE_POINTS',10);

// new session points awarding
define('NEW_SESSION_POINTS', 10);
define('NEW_SESSION_WINDOW_HOURS', 10);

// curvature constants
define('ORIGINAL_POSTER_CURVATURE',0.2);
define('ORIGINAL_POSTER_FUNCTION_SCALE',50);
define('LIKE_CURVATURE',0.2);


// Connects to the database.
function db_connect($is_connection_required) {
    $link = mysql_connect("localhost:3306", "sportstalk", "sportstalk");
    if (!$link) {
        if ($is_connection_required) {
            return_fatal("Couldn't connect to database.");
        } else {
            return null;
        }
    }
    
    if (!mysql_selectdb("sportstalk", $link)) {
        if ($is_connection_required) {
            return_fatal("Couldn't connect to database.");
        } else {
            return null;
        }
    }
    
    if (!mysql_query("SET autocommit=0;", $link)) {
        if ($is_connection_required) {
            return_fatal("Couldn't connect to database.");
        } else {
            return null;
        }
    }
    
    if (!mysql_query("START TRANSACTION WITH CONSISTENT SNAPSHOT;", $link)) {
        if ($is_connection_required) {
            return_fatal("Couldn't connect to database.");
        } else {
            return null;
        }
    }
    
    return $link;
}

// Commits a transaction.
function db_commit() {
    return _db_check(mysql_query("COMMIT;"));
}

// Rolls a transaction back.
function db_rollback() {
    return mysql_query("ROLLBACK;");
}

// Gets the cookie number.
function db_get_cookie_number() {
    $result = _db_check(mysql_query("SELECT value FROM cookie_number"));
    return (string)_db_check(mysql_result($result, 0));
}

// Returns the "current" (i.e. default) event id, or -1 if there is no current event.
function db_get_current_event_id() {
    $result = _db_check(mysql_query("SELECT id from events WHERE UNIX_TIMESTAMP() BETWEEN start_time AND end_time AND is_public = 1 ORDER BY start_time ASC"));
    if (mysql_num_rows($result) == 0) {
        return -1;
    } else {
        return mysql_result($result, 0);
    }
}

function db_get_all_events() {
    $result = _db_check(mysql_query("SELECT id, title, is_public, start_time, end_time FROM events ORDER BY id DESC"));
    return _db_typed_array($result);
}

function db_get_event_name($event_id) {
    $event_id = _db_int($event_id);
    $result = _db_check(mysql_query("SELECT title FROM events WHERE id = $event_id"));
    return mysql_result($result, 0);
}

function db_get_public_events() {
    $result = _db_check(mysql_query("SELECT id, title, is_current FROM events WHERE is_public = 1 ORDER BY id DESC"));
    return _db_typed_array($result);
}

function db_create_event($title) {
    $title = _db_string($title);
    _db_check(mysql_query("INSERT INTO events (title) VALUES (\"$title\")"));
    return true;
}

function db_make_event_public($id) {
    $id = _db_int($id);
    _db_check(mysql_query("UPDATE events SET is_public = 1 WHERE id = $id"));
    _db_check(mysql_affected_rows() == 1);
    return true;
}

function db_set_event_time($id, $start_time, $end_time) {
    $id = _db_int($id);
    $start_time = _db_int($start_time);
    $end_time = _db_int($end_time);
    _db_check(mysql_query("UPDATE events SET start_time = $start_time, end_time = $end_time WHERE id = $id"));
    _db_check(mysql_affected_rows() == 1);
    return true;
}

// Takes a username and looks up the user id.
function db_get_user_id($username) {
    $username = _db_string($username);
    
    $result = _db_check(mysql_query("SELECT id FROM users WHERE username = \"$username\""));
    if (!$result || mysql_num_rows($result) == 0) {
        return -1;
    } else {
        return mysql_result($result, 0);
    }
}

// update user table
function db_update_user_time($user_id, $time)
{
    $user_id = _db_int($user_id);
    $time = _db_int($time);
    $result = _db_check(mysql_query("UPDATE users SET last_update=FROM_UNIXTIME($time) WHERE id = $user_id"));
    return "";
}

// check if the user is banned
function db_is_user_banned($user_id) {
    $user_id = _db_int($user_id);
    
    $result = _db_check(mysql_query("SELECT is_banned FROM users WHERE id = $user_id"));
    _db_check(mysql_num_rows($result) == 1);
    
    return mysql_result($result, 0) == 1;
}

// Set the banned bit of a user.
function db_set_user_banned_bit($user_id, $value) {
    $user_id = _db_int($user_id);
    $value = _db_int($value);
    
    $result = _db_check(mysql_query("UPDATE users SET is_banned = $value WHERE id = $user_id"));
    return TRUE;
}

// Returns an array of all users.
function db_get_all_users() {
    $result = _db_check(mysql_query("SELECT id, facebook_id, username, last_update, is_banned FROM users"));
    return _db_typed_array($result);
}

// get number of users who have updated recently
function db_get_active_users($time, $offset)
{
    $time = _db_int($time);
    $offset = _db_int($offset);
    
    $threshold = $time - $offset;
    $result = _db_check(mysql_query("SELECT id FROM users WHERE last_update IS NOT NULL AND last_update > FROM_UNIXTIME($threshold)"));
    return (int)_db_check(mysql_num_rows($result));
}


// Inserts a post.  Returns TRUE/FALSE on failure.
function db_add_post($author_id, $post, $event_id) {
    $author_id = _db_int($author_id);
    $post = _db_string($post);
    $event_id = _db_int($event_id);
    
    _db_check(mysql_query("INSERT INTO posts (event_id, post, author_id) VALUES ($event_id, \"$post\", $author_id)"));
    $update_id = mysql_insert_id();
    
    return $update_id;
}


// Inserts a reply. Returns TRUE/FALSE on failure.
function db_add_reply($author_id, $post_id, $text) {
    $author_id = _db_int($author_id);
    $post_id = _db_int($post_id);
    $text = _db_string($text);
    
    $result = _db_check(mysql_query("SELECT id FROM posts WHERE id = $post_id"));
    _db_check(mysql_num_rows($result) == 1);
    
    // Add the reply itself.
    _db_check(mysql_query("INSERT INTO replies (post, post_id, author_id) VALUES (\"$text\", $post_id, $author_id)"));

    $assigned_reply_id = mysql_insert_id();
    
    // Mark the post as updated.
    _db_check(mysql_query("UPDATE posts SET replycount = replycount + 1, lastreply = CURRENT_TIMESTAMP WHERE id = $post_id"));
    
    _db_check(mysql_query("INSERT INTO updates (post_id) VALUES ($post_id)"));
    
    $update_id = mysql_insert_id();
    _db_check(mysql_query("DELETE FROM updates WHERE post_id = $post_id AND id < $update_id"));
    
    return $assigned_reply_id;
}

function db_add_post_like($post_id, $user_id) {
    // TODO: make sure this throws an error if the user has already liked the post.  Otherwise, free points.
    
    $post_id = _db_int($post_id);
    $user_id = _db_string($user_id);
    
    $result = _db_check(mysql_query("SELECT * FROM postlikes WHERE user_id = $user_id AND post_id = $post_id;"));
    
    if (mysql_num_rows($result) === 0)
    {    
        _db_check(mysql_query("INSERT INTO postlikes (user_id, post_id) VALUES ($user_id, $post_id)"));
        _db_check(mysql_query("UPDATE posts SET like_count = like_count + 1 WHERE id = $post_id"));
        
        // trigger an update
        _db_check(mysql_query("INSERT INTO updates (post_id) VALUES ($post_id)"));
        $update_id = mysql_insert_id();
        _db_check(mysql_query("DELETE FROM updates WHERE post_id = $post_id AND id < $update_id"));
    }
    
    return TRUE;
}

function db_add_reply_like($reply_id, $user_id) {
    $reply_id = _db_int($reply_id);
    $user_id = _db_string($user_id);
    
    $result = _db_check(mysql_query("SELECT * FROM replylikes WHERE user_id = $user_id AND reply_id = $reply_id;"));
    
    if (mysql_num_rows($result) === 0)
    { 
        _db_check(mysql_query("INSERT INTO replylikes (user_id, reply_id) VALUES ($user_id, $reply_id)"));
        _db_check(mysql_query("UPDATE replies SET like_count = like_count + 1 WHERE id = $reply_id"));

        // trigger an reply update
        _db_check(mysql_query("INSERT INTO reply_updates (reply_id) VALUES ($reply_id)"));
        $update_id = mysql_insert_id();
        _db_check(mysql_query("DELETE FROM reply_updates WHERE reply_id = $reply_id AND id < $update_id"));
    }

    return TRUE;
}

function db_get_post_reply_count($post_id) {
    $post_id = _db_int($post_id);
    $result = _db_check(mysql_query("SELECT replycount FROM posts WHERE id = $post_id"));
    _db_check(mysql_num_rows($result) === 1);
    return (int)_db_check(mysql_result($result, 0));
}

// Returns array of [reply_id, post_id, author, author_id, post, is_liked].
function db_get_replies($post_id, $last_reply_id, $user_id) {
    $user_id = _db_int($user_id);
    $post_id = _db_int($post_id);
    $last_reply_id = _db_int($last_reply_id);
    
    $result = _db_check(mysql_query(
        "SELECT replies.id, replies.post_id, users.username, replies.post, IF(replylikes.user_id IS NULL,0,1) AS liked, users.facebook_id, users.id, replies.like_count
         FROM replies 
         LEFT OUTER JOIN replylikes 
            ON replies.id = replylikes.reply_id 
                AND replylikes.user_id=$user_id
         INNER JOIN users
            ON replies.author_id = users.id
         WHERE replies.post_id = $post_id 
            AND replies.id > $last_reply_id 
         ORDER BY replies.id ASC"
    ));
     
    return _db_typed_array($result);
}

// Returns array of [update_id, post_id, post_replycount, post_lastreplytime, to_remove].
function db_get_updates($last_update_id, $event_id) {
    $last_update_id = _db_int($last_update_id);
    $event_id = _db_int($event_id);
    
    $result = _db_check(mysql_query(
        "SELECT updates.id, posts.id, posts.replycount, UNIX_TIMESTAMP(posts.lastreply), posts.like_count, posts.to_remove 
         FROM updates
         INNER JOIN posts 
            ON updates.post_id = posts.id 
         WHERE updates.id > $last_update_id
           AND posts.event_id = $event_id"
    ));
    
    return _db_typed_array($result);
}

function db_get_reply_updates($last_reply_update_id, $reply_post_id){
    $last_reply_update_id = _db_int($last_reply_update_id);
    $reply_post_id = _db_int($reply_post_id);
    
    $result = _db_check(mysql_query(
        "SELECT reply_updates.id, replies.id, replies.like_count, replies.post_id 
         FROM reply_updates
         INNER JOIN replies 
            ON reply_updates.reply_id = replies.id 
         WHERE reply_updates.id > $last_reply_update_id
            AND replies.post_id = $reply_post_id" // only look at replies to current post id (others will update on switch)
    ));
    
    return _db_typed_array($result);
}


// Returns array of [post_text, author_username, post_time, post_id, post_replycount, post_lastreplytime, is_liked]
function db_get_posts($last_post_id, $user_id, $event_id) {
    $last_post_id = _db_int($last_post_id);
    $user_id = _db_int($user_id);
    $event_id = _db_int($event_id);
    
    $result = _db_check(mysql_query(
        "SELECT posts.post, users.username, UNIX_TIMESTAMP(posts.post_time), posts.id, posts.replycount, UNIX_TIMESTAMP(posts.lastreply), IF(postlikes.user_id IS NULL,0,1), users.facebook_id, users.id, posts.like_count, posts.to_remove
         FROM posts 
         LEFT OUTER JOIN postlikes
            ON posts.id = postlikes.post_id
                AND postlikes.user_id = $user_id
         INNER JOIN users
            ON users.id = posts.author_id
         WHERE posts.id > $last_post_id
           AND posts.event_id = $event_id
         ORDER BY posts.id ASC"
    ));
    
    return _db_typed_array($result);
}

function db_get_current_time() {
    $result = _db_check(mysql_query("SELECT UNIX_TIMESTAMP(NOW());"));
    return (int)_db_check(mysql_result($result, 0));
}

function db_get_user_reply_ratio($post_id, $user_id, $offset_seconds){
    $post_id = _db_int($post_id);
    $user_id = _db_int($user_id);
    $offset_seconds = _db_int($offset_seconds);
    $result = _db_check(mysql_query(
        "SELECT SUM(IF(author_id = $user_id, 1, 0)) / COUNT(*) AS ratio
         FROM replies 
         WHERE post_id = $post_id 
            AND post_time > DATE_ADD(NOW(), INTERVAL - $offset_seconds SECOND)"
    ));
    $ratio = _db_check(mysql_result($result, 0));
    if ($ratio === NULL) {
        $ratio = 0;
    }
    
    return $ratio;
}

// Returns the user id of the author of a given post.
function db_get_post_author_id($post_id) {
    $post_id = _db_int($post_id);
    $result = _db_check(mysql_query("SELECT author_id FROM posts WHERE id = $post_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

// Returns the event id of a given post.
function db_get_post_event_id($post_id) {
    $post_id = _db_int($post_id);
    $result = _db_check(mysql_query("SELECT event_id FROM posts WHERE id = $post_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

// Returns the number of "likes" a post has received.
function db_get_post_like_count($post_id) {
    $post_id = _db_int($post_id);
    $result = _db_check(mysql_query("SELECT COUNT(1) from postlikes WHERE post_id=$post_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

// Returns the number of "post likes" a user has made.
function db_get_user_post_like_count($user_id){
    $user_id = _db_int($user_id);
    $result = _db_check(mysql_query("SELECT COUNT(1) FROM postlikes WHERE user_id=$user_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

// Returns the user id of the author of a given reply.
function db_get_reply_author_id($reply_id) {
    $reply_id = _db_int($reply_id);
    $result = _db_check(mysql_query("SELECT author_id FROM replies WHERE id = $reply_id"));
    _db_check(mysql_num_rows($result) == 1);
    $author_id = (int)_db_check(mysql_result($result, 0));
    return $author_id;
}

// Returns the number of "likes" a reply has received.
function db_get_reply_like_count($reply_id) {
    $reply_id = _db_int($reply_id);
    $result = _db_check(mysql_query("SELECT COUNT(1) from replylikes WHERE reply_id=$reply_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

// Returns the number of "reply likes" a user has made.
function db_get_user_reply_like_count($user_id){
    $user_id = _db_int($user_id);
    $result = _db_check(mysql_query("SELECT COUNT(1) FROM replylikes WHERE user_id=$user_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

// Returns the post id of a given reply.
function db_get_reply_post_id($reply_id){    
    $reply_id = _db_int($reply_id);
    $result = _db_check(mysql_query("SELECT post_id FROM replies WHERE id = $reply_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

function db_get_points_for_post($post_id) {
    $post_id = _db_int($post_id);
    $result = _db_check(mysql_query("SELECT points FROM posts WHERE id = $post_id"));
    _db_check(mysql_num_rows($result) == 1);
    return (int)_db_check(mysql_result($result, 0));
}

function db_get_point_count($user_id, $event_id) {
    $user_id = _db_int($user_id);
    $event_id = _db_int($event_id);
    
    // Count the points.
    $result = _db_check(mysql_query("SELECT points FROM points WHERE user_id = $user_id AND event_id = $event_id"));    
    
    if (mysql_num_rows($result) === 0) {
        return 0;
    } else {
        return (int)_db_check(mysql_result($result, 0));
    }
}

// TO DO: can change this to be user specific
function db_get_current_post_cost($event_id, $user_id) {
    $minute_window = _db_int(POST_COST_WINDOW_MINUTES);
    $event_id = _db_int($event_id);
    $result = _db_check(mysql_query("SELECT COUNT(1) FROM posts WHERE post_time > DATE_ADD(NOW(), INTERVAL - $minute_window MINUTE) AND id > 1 AND event_id = $event_id"));
    $post_count = (int)_db_check(mysql_result($result, 0));
    
    // add a user component
    $result = _db_check(mysql_query("SELECT COUNT(1) FROM posts WHERE post_time > DATE_ADD(NOW(), INTERVAL - $minute_window MINUTE) AND id > 1 AND author_id = $user_id AND event_id = $event_id"));
    $user_post_count = (int)_db_check(mysql_result($result, 0));   
    
    $overall_cost = 0;
    $individual_cost = 0;
    
    if ($post_count <= POST_RATE_THRESHOLD)
    {
        $overall_cost = 0;
    }
    else 
    {
        $overflow = $post_count - POST_RATE_THRESHOLD;
        $overall_cost = UNIVERSAL_SCALE * min(POST_COST_B + POST_COST_A * $overflow * $overflow, MAX_POST_COST);
    }
    
    if ($user_post_count <= USER_POST_RATE_THRESHOLD)
    {
        $individual_cost = 0;
    }
    else 
    {
        $overflow = $user_post_count - USER_POST_RATE_THRESHOLD;
        $individual_cost = UNIVERSAL_SCALE * min(USER_POST_COST_B + USER_POST_COST_A * $overflow * $overflow, USER_MAX_POST_COST);
    }
    
    return $overall_cost + $individual_cost;
}

// ADMIN FUNCTIONS
function db_set_to_remove_post($post_id,$to_remove)
{
    _db_check(mysql_query("UPDATE posts SET to_remove = $to_remove WHERE id = $post_id"));

    // trigger an update
    _db_check(mysql_query("INSERT INTO updates (post_id) VALUES ($post_id)"));
    $update_id = mysql_insert_id();
    _db_check(mysql_query("DELETE FROM updates WHERE post_id = $post_id AND id < $update_id"));
}
function db_unremove_post($post_id)
{
    _db_check(mysql_query("UPDATE posts SET to_remove = 0 WHERE id = $post_id"));

    // trigger an update
    _db_check(mysql_query("INSERT INTO updates (post_id) VALUES ($post_id)"));
    $update_id = mysql_insert_id();
    _db_check(mysql_query("DELETE FROM updates WHERE post_id = $post_id AND id < $update_id"));
}


class PointAwardReasons
{
    const Debug = 0;
    const Login = 1;
    const ReplyAuthor = 2;
    const ReplyOriginalPoster = 3;
    const ReplyLiker = 4;
    const ReplyLikeAuthor = 5;
    const ReplyLikeOriginalPoster = 6;
    const PostLiker = 7;
    const PostLikeOriginalPoster = 8;
}

function db_get_all_user_points($event_id) {
    $event_id = _db_int($event_id);
    $result = _db_check(mysql_query("
        SELECT user_id, facebook_id, username, points
        FROM points INNER JOIN users ON points.user_id = users.id
        WHERE event_id = $event_id AND user_id > 0
        ORDER BY points DESC
    "));
    
    return _db_typed_array($result);
}

function db_get_all_user_accumulated_points($event_id){
    $event_id = _db_int($event_id);
    $result = _db_check(mysql_query("
        SELECT user_id, facebook_id, username, SUM(points) AS totalpoints 
        FROM points_log INNER JOIN users ON points_log.user_id = users.id 
        WHERE event_id = $event_id AND user_id > 0 GROUP BY user_id ORDER BY totalpoints DESC
    "));
    
    return _db_typed_array($result);
}

function db_award_points($user_id, $points, $post_id, $event_id, $reason) {
    $user_id = _db_int($user_id);
    $event_id = _db_int($event_id);
    $points = _db_int($points);
    $reason = _db_int($reason);

    // Add the record of the points.
    $current_points = db_get_point_count($user_id, $event_id);
    $new_points = $current_points + $points;
    _db_check(mysql_query("DELETE FROM points WHERE user_id = $user_id AND event_id = $event_id"));
    _db_check(mysql_query("INSERT INTO points (points, user_id, event_id) VALUES ($new_points, $user_id, $event_id)"));
    
    _db_check(mysql_query("INSERT INTO points_log (points, user_id, event_id, reason) VALUES ($points, $user_id, $event_id, $reason)"));
    
    if ($post_id != null) {
        $post_id = _db_int($post_id);
        _db_check(mysql_query("UPDATE posts SET points = points + $points WHERE id = $post_id"));
    }
}

function db_spend_points($user_id, $points, $event_id) {
    if ($points == 0) {
        return TRUE;
    }

    $user_id = _db_int($user_id);
    $points = _db_int($points);
    $event_id = _db_int($event_id);
    
    $current_points = db_get_point_count($user_id, $event_id);
    
    if ($points > $current_points) {
        return FALSE;
    }
    
    $new_points = $current_points - $points;
    
    _db_check(mysql_query("UPDATE points SET points = $new_points WHERE event_id = $event_id AND user_id = $user_id"));
    
    return TRUE;
}


// new facebook login functions
function db_check_existing_users($facebook_id){
    $facebook_id = _db_string($facebook_id);
    $result = _db_check(mysql_query("SELECT username FROM users WHERE facebook_id = \"$facebook_id\""));
    return (int)_db_check(mysql_num_rows($result));
}

function db_get_username($facebook_id){
    $facebook_id = _db_string($facebook_id);
    $result = _db_check(mysql_query("SELECT username FROM users WHERE facebook_id = \"$facebook_id\""));
    return _db_check(mysql_result($result, 0));
}

function db_add_user_info($facebook_id, $username, $email){
    $facebook_id = _db_string($facebook_id);
    $username = _db_string($username);
    $email = _db_string($email);
    _db_check(mysql_query("INSERT INTO users (facebook_id, username, email) VALUES (\"$facebook_id\", \"$username\", \"$email\")"));
    return 0;
}

function db_get_id_from_facebook_id($facebook_id){
    $facebook_id = _db_string($facebook_id);
    $result2 = _db_check(mysql_query("SELECT id FROM users WHERE facebook_id = \"$facebook_id\""));
    return (int)_db_check(mysql_result($result2, 0));
}

function db_get_facebook_id_from_id($user_id) {
    $user_id = _db_int($user_id);
    $result = _db_check(mysql_query("SELECT facebook_id FROM users WHERE id = $user_id"));
    _db_check(mysql_num_rows($result) == 1);
    return _db_check(mysql_result($result, 0));
}

function db_get_new_invitation_code() {
    $result = _db_check(mysql_query("SELECT UUID()"));
    _db_check(mysql_num_rows($result) == 1);
    $uuid = mysql_result($result, 0);
    _db_check(mysql_query("INSERT INTO invitations (uuid) VALUES ('$uuid')"));
    return $uuid;
}

function db_check_invitation_code($code) {
    $code = _db_string($code);
    $result = _db_check(mysql_query("SELECT COUNT(1) FROM invitations WHERE uuid = \"$code\""));
    _db_check(mysql_num_rows($result) == 1);
    $count = mysql_result($result, 0);
    return ($count == 1);
}

function db_remove_invitation_code($code) {
    $code = _db_string($code);
    _db_check(mysql_query("DELETE FROM invitations WHERE uuid = \"$code\""));
    return true;
}

function db_add_email_to_waiting_list($email) {
    $email = _db_string($email);
    _db_check(mysql_query("INSERT INTO waiting_list (email) VALUES (\"$email\")"));
    return true;
}

// Check whether current time for the user is > previous update time + offset (used to give users points)
function db_check_new_user_session($user_id)
{
    $time = db_get_current_time();
    $threshold = $time - NEW_SESSION_WINDOW_HOURS * 60 * 60;
    // check whether last update came within window
    $result = _db_check(mysql_query("SELECT id FROM users WHERE id=$user_id AND last_update IS NOT NULL AND last_update > FROM_UNIXTIME($threshold)"));
    return (int)_db_check(mysql_num_rows($result));

}


////////////////////////////
//// Internal Functions ////
////////////////////////////
// These functions should only be used in this file.

function _db_int($value) {
    $value = (int)$value;
    return $value;
}

function _db_string($string) {
    # TODO: disable magic quotes and get rid of stripslashes
    $string = stripslashes($string);
    return mysql_real_escape_string($string);
}

function _db_check($result, $not_equal_to_value = FALSE, $message = "Database error.") {
    if ($result === $not_equal_to_value) {
        db_rollback();
        if (DEVELOPMENT_ENVIRONMENT) {
            return_json_message($message . "\n\n" . mysql_error() . "\n\n" . json_encode(debug_backtrace()));
        } else {
            return_json_message("Database error.");
        }
    } else {
        return $result;
    }
}

function _db_typed_array($result) {
    $output = array();
    while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
        for ($i=0; $i < count($row); $i++) { 
            if (mysql_field_type($result, $i) == "int") {
                $row[$i] = (int)$row[$i];
            }
        }
        array_push($output, $row);
    }
    return $output;
}

?>