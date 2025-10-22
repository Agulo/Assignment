<?php
$conn = new mysqli("localhost", "root", "", "social_platform");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>