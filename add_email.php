<?php

require("common.php");

db_connect(/*is_connection_required*/true);

$email = $_POST["email"];

if (!check_email($email)) {
    return_json(array(-1, "Oops! You entered an invalid e-mail address."));
}

db_add_email_to_waiting_list($email);
db_commit();

return_json(array(0, "Great! We'll send you an invitation when we're accepting new users."));

?>