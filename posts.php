<?php

// inputs: last_post_id, last_update_id
// outputs (JSON): [[array of posts with post id > last_post_id], [array of updates with update id > last_update_id], time]

require("common.php");

prevent_caching();

$last_post_id = (int)$_GET["last_post_id"];
$last_update_id = (int)$_GET["last_update_id"];
$reply_post_id = (int)$_GET["reply_post_id"];
$last_reply_id = (int)$_GET["last_reply_id"];
$event_id = (int)$_GET["event_id"];
$last_reply_update_id = (int)$_GET["last_reply_update_id"];

db_connect(/*is_connection_required*/true);
$user_id = check_user_id(/*is_user_id_required*/false);

$initial_time = time();
$stop_time = $initial_time + 60; // 60 seconds is the maximum time before update.
$previous_post_cost = null;

// Sleep until there are updates.
while (time() < $stop_time) {
    $replies = db_get_replies($reply_post_id, $last_reply_id, $user_id);
    $updates = db_get_updates($last_update_id, $event_id);
    $posts = db_get_posts($last_post_id, $user_id, $event_id);
    $reply_updates = db_get_reply_updates($last_reply_update_id,$reply_post_id);
    $current_event_id = db_get_current_event_id();
    $post_cost = db_get_current_post_cost($event_id, $user_id);
    
    if (count($updates) > 0 || count($posts) > 0 || count($replies) > 0 || count($reply_updates) || $current_event_id != $event_id ||
        ($previous_post_cost != null && $post_cost != $previous_post_cost)) {
        break;
    } else {
        $previous_post_cost = $post_cost;
        db_rollback();
        sleep(1); // 1 second is the minimum time before an update.
    }
}

$time = db_get_current_time();

// do check to award points
if ($user_id != -1)
{
    $old_user_indicator = db_check_new_user_session($user_id);
    if ($old_user_indicator == 0 && $current_event_id == $event_id)
    {
        db_award_points($user_id, UNIVERSAL_SCALE * NEW_SESSION_POINTS, null, $event_id, PointAwardReasons::Login);
    }
    
    $user_points = db_get_point_count($user_id, $event_id);
    db_update_user_time($user_id, $time);
} else {
    $user_points = 0;
}

// find number of active users
$num_users = db_get_active_users($time, 120);

// check if the user is banned
$is_banned = false;
if ($user_id != -1 ) {
    $is_banned = db_is_user_banned($user_id);
}

db_commit();

return_json(array($posts, $updates, $replies, $time, $user_points, $post_cost, $num_users, $reply_updates, $current_event_id, $is_banned));

?>
