<?php
// Uncomment for latency test.
// sleep(rand(1,4));

define('DEVELOPMENT_ENVIRONMENT', true);
define('FACEBOOK_APPID', '171474939585250');

require("data.php");

function require_superuser_access() {
    if (DEVELOPMENT_ENVIRONMENT) {
        return true;
    }
    $user_id = check_user_id(/*is_user_id_required*/true);
    $facebook_id = db_get_facebook_id_from_id($user_id);
    if ($facebook_id === "29780" || $facebook_id === "29876" || $facebook_id === "30876") {
        return true;
    } else {
        return_fatal("You do not have access to this page.");
    }
}

function return_fatal($msg = "Unhandled failure.") {
    if (DEVELOPMENT_ENVIRONMENT) {
        // TODO: log this
        return_json_message("Failure: $msg");
    } else {
        return_json("Internal error.");
    }
}

function return_json_message($msg) {
    return_json(array($msg));
}

function return_json($array = array()) {
    header("Content-Type: application/json");
    echo json_encode($array);
    exit(0);
}

function check($condition) {
    if (!$condition) {
        // TODO: use debug_backtrace to be more explicit.
        var_dump(debug_backtrace());
        echo "ERROR:";
        die(mysql_error());
    }
}

function prevent_caching() {
    header("Expires: -1");
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");
}

function generateSalt()
{
    $salt = substr(md5(uniqid(rand(), true)), 0, 10);
    return $salt;
}
function generateHash($plainText, $salt)
{
    return sha1($salt . $plainText);
}

function email_check($email) {
    if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) {
      return true;
    }
    else {
      return false;
    }
}

function check_user_id($is_user_id_required){
    $raw_user_id = $_COOKIE["user_id"];

    //validate the cookie
    list($hashed2, $user_id) = explode("-", $raw_user_id);
    
    $check_user_id = hash_hmac('sha1', db_get_cookie_number() . $user_id, '04EO6t6TdmvgF0x8UpBWAFkChFVYqpdF28hyWBnx');

    if (strcmp($hashed2,$check_user_id) == 0) {
        return (int) $user_id;
    } else if ($is_user_id_required) {
        return_json_message("You are not logged in.");
    } else {
        return -1;
    }
}

function sweetspot($ratio)
{
    $scorearray = array(0.4*SWEETSPOT_SCALE,0.8*SWEETSPOT_SCALE,2*SWEETSPOT_SCALE,1.2*SWEETSPOT_SCALE,0.6*SWEETSPOT_SCALE,0.4*SWEETSPOT_SCALE,0.35*SWEETSPOT_SCALE,0.3*SWEETSPOT_SCALE,0.2*SWEETSPOT_SCALE,0,0);    
    return $scorearray[round(10*$ratio)];
}

function sportstalk_increment($curr,$p,$K)
{
    return round($K*(pow($curr+1,-$p)-pow($curr+2,-$p)));
    //K(-(n+2)^-p+(n+1)^-p)
}

function sportstalk_point_increment($curr,$inc,$p,$K)
{
    return round($K*(pow($curr+1,-$p)-pow($curr+1+$inc,-$p)));
    //K(-(n+2)^-p+(n+1)^-p)
}


function send_email($to, $subject, $message) {
    $headers = 'From: noreply@camonacho.com' . "\r\n" .
        'Reply-To: noreply@camonacho.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    return mail($to, $subject, $message, $headers);
}

/*
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.

Taken from:
http://www.linuxjournal.com/article/9585?page=0,3
*/
function check_email($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

?>