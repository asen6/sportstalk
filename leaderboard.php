<?php

require("common.php");

prevent_caching();

$event_id = (int)$_GET["event_id"];

if ($event_id <= 0) {
	return_json(array());
}

db_connect(true);

return_json(db_get_all_user_accumulated_points($event_id));

?>