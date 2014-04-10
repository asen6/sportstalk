<?php

require("common.php");

if (!DEVELOPMENT_ENVIRONMENT) {
	return_json_message("Sorry.");
}

db_connect(/*is_connection_required*/true);

require_superuser_access();

$user_id = check_user_id(/*is_user_id_required*/true);

$points = (int)$_GET['points'];

if ($points == 0) {
    $points = 100;
}

db_award_points($user_id, 100 * $points, null, db_get_current_event_id(), PointAwardReasons::Debug);

db_commit();

return_json_message("Got $points points!");

?>