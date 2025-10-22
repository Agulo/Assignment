<?php
session_start();
include 'internet.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $target_file = $target_dir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $content, $target_file);
            $stmt->execute();
            //echo "Picture posted successfully!";
        } else {
            echo "Error uploading image!";
        }
    } else {
        echo "No image selected!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
      * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f0f2f5;
}
a{
text-decoration: none;
color: white;
}
.navbar {
    background-color: #333;
    color: white;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
}

.profile .name {
    font-size: 1.1rem;
    font-weight: 500;
}

.btn-change,
.btn-logout {
    padding: 8px 12px;
    background-color: #4caf50;
    border: none;
    border-radius: 4px;
    color: white;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.btn-change:hover,
.btn-logout:hover {
    background-color: #45a049;
}

    </style>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Social Platform</title>
</head>
<body>
  <nav class="navbar">
    <div class="profile">
      <img id="profile-img" src="profile.png" alt="Profile Picture" />
      <div class="name" id="profile-name"><?php echo $_SESSION['username']; ?></div>
      <button class="btn-change" id="change-btn">Change Profile</button>
    </div>
    <button class="btn-logout" id="logout-btn"><a href="login.html">Logout</a></button>
  </nav>

  <input type="file" id="file-input" accept="image/*" style="display:none" />

  <form action="Dashboard.php" method="post" enctype="multipart/form-data">
        <label for="content">Caption:</label>
        <input type="text" name="content" id="content"><br><br>

        <label for="image">Select image:</label>
        <input type="file" name="image" id="image" required><br><br>

        <button type="submit">Post Picture</button>
    </form>

  <script>
    const changeBtn = document.getElementById('change-btn');
    const fileInput = document.getElementById('file-input');
    const profileImg = document.getElementById('profile-img');
    const profileName = document.getElementById('profile-name');

    changeBtn.addEventListener('click', () => {
      fileInput.click();
    });

    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = () => {
        profileImg.src = reader.result;
      };
      reader.readAsDataURL(file);

    });

    
  </script>
</body>
</html>