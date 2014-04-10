<?php

require("common.php");

prevent_caching();

db_connect(/*is_connection_required*/true);
$post_id = $_GET['post_id'];
$user_id = check_user_id(/*is_user_id_required*/true);

if (db_is_user_banned($user_id)) {
    return_json_message("Oops! Your account has been temporarily banned.  Please contact banned@quobit.com to have your ban lifted.");
}

$post_author_id = db_get_post_author_id($post_id);

$current_event_id = db_get_current_event_id();
if (db_get_post_event_id($post_id) != $current_event_id) {
    return_json_message("You cannot like a post that is not part of the current event.");
}

if ($user_id === $post_author_id) {
    return_json_message("You cannot like your own post.");
}

// Award points to the author of the post.
$post_like_count = db_get_post_like_count($post_id);
$post_points = sportstalk_increment($post_like_count, LIKE_CURVATURE, UNIVERSAL_SCALE * MAX_POSTER_LIKE_POINTS);
if ($post_points > 0){
    db_award_points($post_author_id, $post_points, null, $current_event_id, PointAwardReasons::PostLikeOriginalPoster);
}

// Award points to the user who liked the post.
// FIXME: Shouldn't this only be related to number of likes in the last n minutes or something?
$user_post_like_count = db_get_user_post_like_count($user_id);
$user_points = sportstalk_increment($user_post_like_count, LIKE_CURVATURE, UNIVERSAL_SCALE * MAX_POST_LIKE_POINTS);
if ($user_points > 0){
    db_award_points($user_id, $user_points, $post_id, $current_event_id, PointAwardReasons::PostLiker);
}

db_add_post_like($post_id, $user_id);
db_commit();

return_json(array($user_id, $post_id));

?>