<?php

require("common.php");
prevent_caching();

db_connect(/*is_connection_required*/true);
check_user_id(/*is_user_id_required*/true);

// TODO: Whenever there is an actual interface for this, make this a POST.
//$email = $_GET['email'];

//if (!check_email($email)) {
//    return_json_message("Invalid e-mail address.");
//}

// TODO: want to limit number of invitations?
$code = db_get_new_invitation_code();
db_commit();

//send_email($email, "camonacho invitation", "You've been invited to join camonacho!  Join by clicking the link below:\n\nhttp://www.camonacho.com/?invitation=$code");
echo "http://yankees.quobit.com/?invitation=$code";
//return_json_message("The invitation has been sent. $code");

?>
