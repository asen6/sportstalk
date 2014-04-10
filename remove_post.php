<?php

require("common.php");

db_connect(/*is_connection_required*/true);

$id = $_POST["id"];
$to_remove = $_POST["to_remove"];

db_set_to_remove_post($id, $to_remove);
db_commit();
return_json(array($id));

?>