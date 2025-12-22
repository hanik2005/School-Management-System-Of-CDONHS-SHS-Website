<?php
    // Database configuration variables
    $db_host = 'localhost';
    $db_user = 'root';
    $db_password = '';
    $db_name = 'cdonhs_shs_database_2.0';

    // Create connection using MySQLi
    $connection = new mysqli($db_host, $db_user, $db_password, $db_name);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Set charset to UTF-8
    $connection->set_charset("utf8");
?>