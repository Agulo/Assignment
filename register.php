<?php
include 'internet.php'; // your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Check if passwords match
    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!'); window.location='register.html';</script>";
        exit();
    }

    // Check if username or email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE full_name = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username or email already exists!'); window.location='register.html';</script>";
        exit();
    } 

    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($insert_stmt->execute()) {
        header("Location: login.html");
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $insert_stmt->close();
}

$conn->close();
?>
