<?php
include 'internet.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$email'");
    if ($check->num_rows > 0) {
        echo "Username or email already exists!";
    } else {
        $insert = $conn->query("INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')");
        if ($insert) {
            echo "Registration successful! You can now login.";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>