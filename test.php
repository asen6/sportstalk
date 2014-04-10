<?php

require("common.php");

db_connect(true);
echo db_get_points_for_post(1);

?>