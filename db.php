<?php

$host = "localhost";
$user = "root";
$password = "YourNewPassword";
$database = "cake_shop_inventory";
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

?>