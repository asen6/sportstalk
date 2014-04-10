<?php

require("common.php");

db_connect(/*is_connection_required*/true);

$post = $_POST["post"];
$event_id = $_POST["event_id"];
$dummy_id = $_POST["dummy_id"];

// use -1 for twitter author id's
$assigned_post_id = db_add_post($dummy_id, $post, $event_id, $dummy_id);

db_commit();
return_json(array($assigned_post_id, ""));

?>