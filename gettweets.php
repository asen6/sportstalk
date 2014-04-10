<?php

require("common.php");

$peoplearray = array("Buster_ESPN","ClaytonESPN","AdamSchefter","ESPNStatsInfo","mortreport");
$peopleidarray = array(-1,-2,-3,-4,-5);


$counter = 0;
$tweet_array = array();

for ($i=0; $i < count($peoplearray); $i++){
    $tweet_array = getTwitterStatus($peoplearray[$i],$tweet_array,$peopleidarray[$i]);
}

return_json($tweet_array);
// maybe strip @, # and filter out retweets

function getTwitterStatus($userid, $result, $dummy_id){
    $url = "http://twitter.com/statuses/user_timeline/$userid.xml?count=5";

    $xml = simplexml_load_file($url) or die("could not connect");

    foreach($xml->status as $status){
       $text = $status->text;
       $id = $status->id;
       $retweeted = $status->retweeted;
       $retweet_count = $status->retweet_count;
       $name = $status->user->name;
       $imgsource = $status->user->profile_image_url;
       
       $result[count($result)] = array("$text","$name","$imgsource",$dummy_id);
    }
    return $result;
 }
