<?php

require("common.php");

db_connect(/*is_connection_required*/true);
$author_id = check_user_id(/*is_user_id_required*/true);

if (db_is_user_banned($author_id)) {
    return_json(array(-1,"Oops! Your account has been temporarily banned.  Please contact banned@quobit.com to have your ban lifted."));
}

// TODO: strip whitespace from post?
$post = $_POST["post"];
$event_id = $_POST["event_id"];
$threshold = db_get_current_post_cost($event_id, $author_id);

if ($event_id !== db_get_current_event_id()) {
    return_json(array(-1,"That event is now closed."));
}

$user_points = db_get_point_count($author_id, $event_id);

if (strlen($post) == 0) {
    return_json(array(-1,"You cannot make an empty post."));
}

if ($user_points < $threshold) {
    return_json(array(-1,"Not enough points to post."));
}

$assigned_post_id = db_add_post($author_id, $post, $event_id);
db_spend_points($author_id, $threshold, $event_id);

db_commit();
return_json(array($assigned_post_id, ""));

?>