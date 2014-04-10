<?php

require("common.php");

db_connect(/*is_connection_required*/true);
$post = $_POST["post"];
$post_id = $_POST["post_id"];
$user_id = check_user_id(/*is_user_id_required*/true);

if (strlen($post) == 0) {
    return_json(array(1, "You cannot make an empty reply."));
}

if (db_is_user_banned($user_id)) {
    return_json_message("Oops! Your account has been temporarily banned.  Please contact banned@quobit.com to have your ban lifted.");
}

// Award points to the replier and the original poster.
$reply_count = db_get_post_reply_count($post_id);
$post_author_id = db_get_post_author_id($post_id);
$existing_post_points = db_get_points_for_post($post_id);
$current_event_id = db_get_current_event_id();
if (db_get_post_event_id($post_id) != $current_event_id) {
    return_json(array(1, "You cannot reply to a post that is not part of the current event."));
}

// Consider the reply ratio in the last two minutes.
$ratio = db_get_user_reply_ratio($post_id, $user_id, REPLY_RATIO_SECONDS);

// If the user is making the first reply to his own post, make the ratio 1, not 0.
if ($reply_count == 0 && $user_id == $post_author_id) {
    $ratio = 1;
}

// Compute points for the replier according to k(x)*f(n) as outlined in sportstalk document.
$points_for_reply = round(sweetspot($ratio));
if ($points_for_reply > 0) {
    db_award_points($user_id, $points_for_reply, null, $current_event_id, PointAwardReasons::ReplyAuthor);
}

// Award points to the original poster as well.
$points_for_post = sportstalk_point_increment($existing_post_points/ORIGINAL_POSTER_FUNCTION_SCALE, $points_for_reply/ORIGINAL_POSTER_FUNCTION_SCALE, ORIGINAL_POSTER_CURVATURE, UNIVERSAL_SCALE * MAX_ORIGINAL_POSTER_POINTS);
if ($points_for_post > 0) {
    db_award_points($post_author_id, $points_for_post, $post_id, $current_event_id, PointAwardReasons::ReplyOriginalPoster);
}

$assigned_reply_id = db_add_reply($user_id, $post_id, $post);
db_commit();

return_json(array($assigned_reply_id));

?>