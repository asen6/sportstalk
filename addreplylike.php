<?php

require("common.php");

prevent_caching();

db_connect(/*is_connection_required*/true);
$reply_id = $_GET['reply_id'];
$user_id = check_user_id(/*is_user_id_required*/true);

if (db_is_user_banned($user_id)) {
    return_json_message("Oops! Your account has been temporarily banned.  Please contact banned@quobit.com to have your ban lifted.");
}

$reply_author_id = db_get_reply_author_id($reply_id);

if ($user_id === $reply_author_id) {
    return_json_message("You cannot like your own reply.");
}

$reply_post_id = db_get_reply_post_id($reply_id);
$post_author_id = db_get_post_author_id($reply_post_id);
$current_event_id = db_get_current_event_id();
if (db_get_post_event_id($reply_post_id) != $current_event_id) {
    return_json_message("You cannot reply to a post that is not part of the current event.");
}

// Award points to the author of the reply.
$reply_like_count = db_get_reply_like_count($reply_id);
$reply_points = sportstalk_increment($reply_like_count, LIKE_CURVATURE, UNIVERSAL_SCALE * MAX_REPLY_AUTHOR_LIKE_POINTS);
if ($reply_points > 0){
    db_award_points($reply_author_id, $reply_points, null, $current_event_id, PointAwardReasons::ReplyLikeAuthor);
}

// Award points to the user who liked the reply.
// FIXME: Shouldn't this only be related to number of likes in the last n minutes or something?
$user_reply_like_count = db_get_user_reply_like_count($user_id);
$user_points = sportstalk_increment($user_reply_like_count, LIKE_CURVATURE, UNIVERSAL_SCALE * MAX_REPLY_LIKE_POINTS);
if ($user_points > 0){
    db_award_points($user_id, $user_points, null, $current_event_id, PointAwardReasons::ReplyLiker);
}

// Award points to the original poster.
$existing_post_points = db_get_points_for_post($reply_post_id);

$post_points = sportstalk_point_increment($existing_post_points/ORIGINAL_POSTER_FUNCTION_SCALE, ($reply_points + $user_points)/ORIGINAL_POSTER_FUNCTION_SCALE, ORIGINAL_POSTER_CURVATURE, UNIVERSAL_SCALE * MAX_ORIGINAL_POSTER_POINTS);

if ($post_points) {
    db_award_points($post_author_id, $post_points, $post_id, $current_event_id, PointAwardReasons::ReplyLikeOriginalPoster);
}

// Add the reply to the database and commit the changes.
db_add_reply_like($reply_id, $user_id);
db_commit();
return_json();
?>