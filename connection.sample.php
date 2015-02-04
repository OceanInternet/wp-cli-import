<?php

$servername = "localhost";
$username   = "old-db-user";
$password   = "old-db-pass";
$dbname     = "old-db-name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    exit;
}