<?php

require("common.php");

prevent_caching();

db_connect(true);

return_json(db_get_public_events());

?>
