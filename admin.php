<?php

require("common.php");
db_connect(/*is_connection_required*/true);
require_superuser_access();

$action = $_POST["action"];

if ($action !== null) {
    switch ($action) {
        case "set_time":
            $id = $_POST["id"];
            $start_time = strtotime($_POST["start_time"]);
            $end_time = strtotime($_POST["end_time"]);

            if ($start_time == FALSE || $end_time == FALSE) {
                return_json_message("couldn't parse the times");
            }

            if ($start_time > $end_time) {
                $end_time = $start_time;
            }

            db_set_event_time($id, $start_time, $end_time);
            db_commit();
            return_json_message("success");
            break;
        case "make_public":
            $id = $_POST["id"];
            db_make_event_public($id);
            db_commit();
            return_json_message("success");
            break;
        case "create_new":
            $name = $_POST["name"];
            db_create_event($name);
            db_commit();
            return_json_message("\"$name\" created.");
            break;
        case "set_user_ban":
            $id = $_POST["id"];
            $value = $_POST["value"];
            db_set_user_banned_bit($id, $value);
            db_commit();
            return_json_message("user $id set to banned = $value");
            break;
    }
    return_json_message("Bad action.");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <script src="js/jquery-1.5.2.min.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript">
            function do_action(action, args) {
                $.post(window.location, "action=" + action + "&" + args, function(result) {
                        alert(result);
                        window.location = window.location;
                    }
                );
            }
        
            function set_time(id, start_time, end_time) { 
                do_action("set_time", "id=" + id + "&start_time=" + start_time + "&end_time=" + end_time);
            }
            
            function make_public(id) {
                do_action("make_public", "id=" + id);
            }
            
            function add_event(name) {
                do_action("create_new", "name=" + name);
            }
            
            function set_user_ban(id, value) {
                do_action("set_user_ban", "id=" + id + "&value=" + value);
            }

            function ask_for_time(id, start_time, end_time) {
                var original_start_time = start_time;
                start_time = prompt('start time:', start_time);
                end_time = prompt('end time:', original_start_time == start_time ? end_time : start_time);
                set_time(id, start_time, end_time);
            }

        </script>
        <style type="text/css">
            .current {
                font-weight:bold;
            }
        </style>
    </head>
    <body>
        <h3>Events</h3>
        <button onclick="add_event(prompt('event name:'));">create event</button>
        <?php
        
            $events = db_get_all_events();
            $current_event_id = db_get_current_event_id();
            $count = count($events);
            ?>
            <table id="events">
            <?php
            for ($i = 0; $i < $count; $i++) { 
                $event = $events[$i];
                $event_id = $event[0];
                $event_title = $event[1];
                $is_public = $event[2];
                $is_current = ($event_id == $current_event_id);

                $start_time = null;
                $FORMAT_STRING = "m/d/y g:i A T";
                if ($event[3] !== null) {
                    $start_time = date($FORMAT_STRING, $event[3]);
                }
                $end_time = null;
                if ($event[4] !== null) {
                    $end_time = date($FORMAT_STRING, $event[4]);
                }
                
                if ($is_current) {
                    echo "<tr class=\"event current\">";
                } else {
                    echo "<tr class=\"event\">";
                }
                echo "<td>$event_title</td>";
                
                if ($is_public !== 1) {
                    echo "<td><button onclick=\"make_public($event_id)\">make public</button></td>";
                } else {
                    echo "<td>[PUBLIC]</td>";
                }

                echo "<td>$start_time</td><td>$end_time</td><td><button onclick=\"ask_for_time($event_id, '$start_time', '$end_time')\">change time</button></td>";
                
                echo "</tr>";
            }

            ?></table><?php
            
        ?>
        <hr />
        <table id="users">
            <tr><th>User ID</th><th>Facebook ID</th><th>Name</th><th>Last Update</th><th>Is Banned</th></tr>
        <?php
            
            $users = db_get_all_users();
            $count = count($users);
            for ($i = 0; $i < $count; $i++) {
                $user = $users[$i];
                $user_id = $user[0];
                $facebook_id = $user[1];
                $name = $user[2];
                $last_update = $user[3];
                $is_banned = $user[4];
        ?>
            <tr><td><?=$user_id?></td><td><?=$facebook_id?></td><td><?=$name?></td><td><?=$last_update?></td><td><?=$is_banned?></td><td><button onclick="set_user_ban(<?=$user_id?>, <?=1 - $is_banned?>);">toggle ban</button></td></tr>
        <?php
            }
            
        ?>
        </table>
    </body>
</html>