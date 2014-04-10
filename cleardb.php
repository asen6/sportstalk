<?php

require("common.php");

db_connect(/*is_connection_required*/true);

require_superuser_access();

/*check(mysql_query("DELETE FROM posts");
check(mysql_query("DELETE FROM updates");
check(mysql_query("DELETE FROM replies");*/

?><!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
</head>
<body>
    Did not clear posts and replies.
    <p><a href="/">Return Home</a></p>
</body>
</html>