<?php
session_start();
include 'internet.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE full_name='$username' LIMIT 1");

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['full_name'];
            echo "Login successful! Welcome, " . $user['full_name'];
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "Username not found!";
    }
}
?>
