<?php

require("common.php");
require 'fb/facebook.php';

prevent_caching();

db_connect(true);
$accesstoken = $_GET['accesstoken'];
$invitation = $_GET['invitation'];

// initialize our app object
// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => FACEBOOK_APPID,
  'secret' => 'd4c3378bb556c040c923a7c3151adef1',
));

// check if it's valid
$facebook = $facebook->setAccessToken($accesstoken);

// extract information
$facebook_id = $facebook->getUser();

if ($facebook_id == "0")
{
    // invalid access token
    return_json(array(-1, "Oops! We were unable to access facebook.  Please try again later."));
}

// check if the fbid already exists in the database
$existing_matches = db_check_existing_users($facebook_id);

$username = "";
if ($existing_matches == 0)
{
    $user_profile = $facebook->api('/me');
    $username = $user_profile['name'];
    $email = $user_profile['email'];

    // add to our database
    db_add_user_info($facebook_id, $username, $email);
    db_commit();
}
else
{
    // take the username from the result
    $username = db_get_username($facebook_id);
}

$id = db_get_id_from_facebook_id($facebook_id);

$cookie_number = db_get_cookie_number();

// Set the user id cookie.
$expire=time()+60*60*24*365;

$hashed = hash_hmac('sha1', $cookie_number . $id, '04EO6t6TdmvgF0x8UpBWAFkChFVYqpdF28hyWBnx');
setcookie("user_id", $hashed . "-" . $id, $expire);
return_json(array($id, $existing_matches, "$facebook_id", "$username"));

?>