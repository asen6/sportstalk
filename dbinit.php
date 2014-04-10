<?php

require("common.php");

$link = db_connect(/*is_connection_required*/true);

require_superuser_access();

// Require that the user enter the magic number.
if (DEVELOPMENT_ENVIRONMENT) {
    $db_init_value = "";
    $actual_init_value = "";
} else {
    $db_init_value = $_GET["value"];
    $actual_init_value = db_get_cookie_number();
}

if ($db_init_value == $actual_init_value) {
    $version = 0;

    $result = mysql_query("SELECT version from version");
    if ($result) {
        $version = mysql_result($result, 0);
    }

    if (DEVELOPMENT_ENVIRONMENT) {
        // In DEVELOPMENT_ENVIRONMENT, nuke the db.
        $version = 0;
    }

    $original_version = $version;

    if ($version == 0) {
        check(mysql_query("DROP TABLE IF EXISTS posts", $link));
        check(mysql_query(
            "CREATE TABLE posts 
                (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 event_id INT NOT NULL,
                 post TEXT,
                 author_id INT NOT NULL,
                 post_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                 replycount INT NOT NULL DEFAULT 0,
                 lastreply TIMESTAMP DEFAULT 0,
                 points INT NOT NULL DEFAULT 0,
                 like_count INT NOT NULL DEFAULT 0,
                 to_remove TINYINT(1) NOT NULL DEFAULT 0) type=InnoDB;", $link));
        
        check(mysql_query("DROP TABLE IF EXISTS events", $link));
        check(mysql_query(
            "CREATE TABLE events
                (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 title VARCHAR(255) NOT NULL,
                 is_public TINYINT(1) NOT NULL DEFAULT 0,
                 is_current TINYINT(1) NOT NULL DEFAULT 0) type=InnoDB", $link));
        
        check(mysql_query("INSERT INTO events (title, is_current, is_public) VALUES ('initial event', 1, 1)"));
                 
        check(mysql_query("DROP TABLE IF EXISTS updates", $link));
        check(mysql_query(
            "CREATE TABLE updates 
                (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 post_id INT NOT NULL,
                 INDEX updates_post_id (post_id)) type=InnoDB"));

        check(mysql_query("DROP TABLE IF EXISTS reply_updates", $link));
        check(mysql_query(
            "CREATE TABLE reply_updates 
                (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 reply_id INT NOT NULL,
                 INDEX updates_reply_id (reply_id)) type=InnoDB"));
             
                 
                 
        check(mysql_query("DROP TABLE IF EXISTS replies", $link));
        check(mysql_query(
            "CREATE TABLE replies 
                (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 post_id INT NOT NULL,
                 post TEXT, 
                 author_id INT NOT NULL,
                 post_time TIMESTAMP,
                 like_count INT NOT NULL DEFAULT 0) type=InnoDB;", $link));

        check(mysql_query("DROP TABLE IF EXISTS users", $link));
        check(mysql_query(
            "CREATE TABLE users 
                (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 facebook_id VARCHAR(255) NOT NULL,
                 username VARCHAR(255),  
                 email VARCHAR(255), 
                 last_update DATETIME,
                 is_banned TINYINT(1)) type=InnoDB;", $link));
        
        check(mysql_query("INSERT INTO users (id,facebook_id,username) VALUES (-1, '-1', 'Buster Olney')"));
        check(mysql_query("INSERT INTO users (id,facebook_id,username) VALUES (-2, '-1', 'John Clayton')"));
        check(mysql_query("INSERT INTO users (id,facebook_id,username) VALUES (-3, '-1', 'Adam Schefter')"));
        check(mysql_query("INSERT INTO users (id,facebook_id,username) VALUES (-4, '-1', 'ESPN Stats & Info')"));
        check(mysql_query("INSERT INTO users (id,facebook_id,username) VALUES (-5, '-1', 'Chris Mortensen')"));
        
        check(mysql_query("DROP TABLE IF EXISTS friends", $link));
        check(mysql_query(
            "CREATE TABLE friends 
                (id1 INT,
                 id2 int, 
                 PRIMARY KEY (id1,id2)) type=InnoDB;", $link));

        // TODO: should post likes and reply likes be separate?  could use a flag in the same table.
        // TODO: likewise for posts and replies

        check(mysql_query("DROP TABLE IF EXISTS postlikes", $link));
        check(mysql_query(
            "CREATE TABLE postlikes
                (user_id INT,
                 post_id INT,
                 PRIMARY KEY (user_id,post_id)) type=InnoDB;", $link));

        check(mysql_query("DROP TABLE IF EXISTS replylikes", $link));
        check(mysql_query(
            "CREATE TABLE replylikes 
                (user_id INT,
                 reply_id INT,
                 PRIMARY KEY (user_id,reply_id)) type=InnoDB;", $link));

        check(mysql_query("DROP TABLE IF EXISTS points", $link));
        check(mysql_query(
            "CREATE TABLE points 
                (event_id INT NOT NULL,
                 user_id INT NOT NULL,
                 points INT,
                 PRIMARY KEY (event_id, user_id)) type=InnoDB;", $link));
        
        check(mysql_query("DROP TABLE IF EXISTS points_log", $link));
        check(mysql_query(
            "CREATE TABLE points_log
                (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 points INT,
                 date_awarded TIMESTAMP,
                 user_id INT,
                 event_id INT,
                 reason INT) type=InnoDB;", $link));

        check(mysql_query("DROP TABLE IF EXISTS cookie_number", $link));
        check(mysql_query("CREATE TABLE cookie_number (value CHAR(36)) type=InnoDB"));
        check(mysql_query("INSERT INTO cookie_number (value) VALUES (UUID())"));

        check(mysql_query("DROP TABLE IF EXISTS invitations", $link));
        check(mysql_query("CREATE TABLE invitations (uuid CHAR(36)) type=InnoDB"));
        
        check(mysql_query("DROP TABLE IF EXISTS waiting_list", $link));
        check(mysql_query(
            "CREATE TABLE waiting_list 
                (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255),
                request_time TIMESTAMP) type=InnoDB;", $link));
        
        check(mysql_query("DROP TABLE IF EXISTS version", $link));
        check(mysql_query("CREATE TABLE version (version INT) type=InnoDB"), $link);
        check(mysql_query("INSERT INTO version (version) VALUES (1)"), $link);

        $version = 1;
    }

    if ($version == 1) {
        check(mysql_query("ALTER TABLE events ADD COLUMN start_time INT AFTER is_current", $link));
        check(mysql_query("ALTER TABLE events ADD COLUMN end_time INT AFTER start_time", $link));
        check(mysql_query("ALTER TABLE events DROP COLUMN is_current", $link));
        check(mysql_query("ALTER TABLE events ADD INDEX (start_time)", $link));
        check(mysql_query("ALTER TABLE events ADD INDEX (end_time)", $link));
        check(mysql_query("UPDATE version SET version = 2", $link));
        $version = 2;
    }

    db_commit();
} else {
    return_json_message("$actual_init_value");
}

?><!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/main.css" type="text/css" media="screen" title="no title" charset="utf-8">
</head>
<body>
    Updated database from version <?= $original_version ?> to <?= $version ?>.
    <p><a href="/">Return Home</a></p>
</body>
</html>