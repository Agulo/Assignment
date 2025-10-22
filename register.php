<?php
include 'internet.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!'); window.location='register.html';</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE full_name = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username or email already exists!'); window.location='register.html';</script>";
        exit();
    } 

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="entry.css">
    <title>Register</title>
</head>
<body>
    <div class="container">
        <div class="register-box">
            <h2>Register</h2>
            <form action="register.php" method="post">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm" required>
                <button type="submit" name="submit">Register</button>
            </form>
            <p class="signin-text">Already a member? <a href="login.html">Sign in</a></p>
        </div>
    </div>
</body>
</html>