<?php
// XAMPP defaults: MySQL on localhost:3306, user root, no password.
// If your MySQL settings differ, change these values here.
$host = "localhost";
$user = "root";
$password = "";
$database = "cake_shop_inventory";
$port = 3306;

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_error) {
    http_response_code(500);
    die("Database connection failed. Check that MySQL is running and the settings in db.php match your XAMPP MySQL configuration.");
}
$conn->set_charset("utf8mb4");
?>
