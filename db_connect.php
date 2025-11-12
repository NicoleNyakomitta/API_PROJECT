<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_db"; // make sure this database exists

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
