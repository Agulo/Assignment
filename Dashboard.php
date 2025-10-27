<?php
session_start();
include 'internet.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $res = $conn->query("SELECT user_id, full_name FROM users WHERE full_name LIKE '%$search%'");
    while ($row = $res->fetch_assoc()) {
        $search_results[] = $row;
    }
}


// Handle post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $content = $_POST['content'];
  $user_id = $_SESSION['user_id'];
  $image_path = null;

  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $target_file = $target_dir . time() . "_" . basename($_FILES['image']['name']);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $image_path = $target_file;
    }
  }

  $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $user_id, $content, $image_path);
  $stmt->execute();

  // Redirect to same page so form resets, message not shown
  header("Location: Dashboard.php");
  exit();
}

$result = $conn->query("SELECT p.content, p.image_path, p.created_at, u.full_name, u.user_id FROM posts p
                        JOIN users u ON p.user_id = u.user_id
                        ORDER BY p.created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Social Platform</title>
    <style>
      * {margin: 0; padding: 0; box-sizing: border-box;}
      body {
        font-family: Arial, sans-serif;
        background-color: #f0f2f5;
      }
      a {text-decoration: none; color: white;}
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
      .btn-change, .btn-logout {
        padding: 8px 12px;
        background-color: #0b2041;
        border: none;
        border-radius: 4px;
        color: white;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
      }
      .btn-change:hover, .btn-logout:hover { background-color: #0a5d81ff; }

      form {
        background: white;
        border-radius: 10px;
        width: 500px;
        margin: 40px auto;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }

      textarea {
        width: 100%;
        height: 100px;
        resize: none;
        border: 1px solid #ccc;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
        outline: none;
      }

      .image-preview {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 10px 0;
      }

      .image-preview img {
        max-width: 80%;
        max-height: 250px;
        border-radius: 10px;
        display: none;
        object-fit: cover;
      }

      .actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 10px;
      }

      .actions label { cursor: pointer; }
      .actions img {
        width: 30px;
        height: 30px;
        opacity: 0.7;
        transition: 0.2s;
      }
      .actions img:hover { opacity: 1; }
      .actions button {
        background: #0b2041;
        border: none;
        color: white;
        padding: 8px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: 0.3s;
      }
      .actions button:hover { background: #0a5d81ff; }

      /* Posts list */
      .posts {
        width: 500px;
        margin: 0 auto 50px;
      }
      .posts h4 a {
        color: #0b2041;
        text-decoration: none;
        cursor: pointer;
        font-weight: 600;
      }

      .posts h4 a:hover {
        color: #0a5d81ff;
        text-decoration: underline;
      }

      .post {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-top: 20px;
      }
      .post h4 {margin-bottom: 10px; color: #0b2041;}
      .post p {margin-bottom: 10px;}
      .post img {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 10px;
      }
    </style>
  </head>
<body>
  <nav class="navbar">
      <div class="profile">
        <img id="profile-img" src="profile.png" alt="Profile Picture"/>
        <div class="name" id="profile-name"><?php echo $_SESSION['username']; ?></div>
        <button class="btn-change" id="change-btn">Change Profile</button>
      </div>

      <form method="GET" style="display:flex; align-items:center; margin:1px; height: 1px; width: 225px">
        <input type="text" name="search" placeholder="Search..." 
              style=" padding:2px 6px; font-size:0.8rem; border:none; border-radius:3px; outline:none;">
        <button type="submit" 
                style="height:24px; padding:2px 6px; font-size:0.8rem; border:none; border-radius:3px; background:#0b2041; color:white; cursor:pointer;">
          Go
        </button>
      </form>
      <?php if (!empty($search_results)): ?>
      <div style="position:absolute; top:50px; left:50%; transform:translateX(-50%); 
                width:200px; background:white; border:1px solid #ccc; border-radius:5px; z-index:1000;">
      <ul style="list-style:none; padding:5px; margin:0;">
        <?php foreach ($search_results as $user): ?>
          <li style="padding:5px 8px; border-bottom:1px solid #eee;">
            <a href="message.php?receiver_id=<?php echo $user['user_id']; ?>" 
              style="text-decoration:none; color:#0b2041; font-size:0.9rem;">
              <?php echo htmlspecialchars($user['full_name']); ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <button class="btn-logout"><a href="reset_password.php">reset password</a></button>
    <button class="btn-logout"><a href="logout.php">Logout</a></button>
  </nav>

  <input type="file" id="file-input" accept="image/*" style="display:none" />

  <form action="Dashboard.php" method="POST" enctype="multipart/form-data">
    <textarea name="content" placeholder="Share Something ..."></textarea>

    <div class="image-preview">
      <img id="preview" src="#" alt="Image Preview">
    </div>

    <div class="actions">
      <label for="imageUpload">
        <img src="download.png" alt="Upload Icon">
      </label>
      <input type="file" id="imageUpload" name="image" accept="image/*" style="display:none;">
      <button type="submit">POST</button>
    </div>
  </form>

  <div class="posts">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="post">
        <h4>
          <a href="message.php?receiver_id=<?php echo $row['user_id']; ?>">
          <?php echo htmlspecialchars($row['full_name']); ?></a>
        </h4>


        <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
        <?php if (!empty($row['image_path'])): ?>
          <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Post Image">
        <?php endif; ?>
        <small>Posted on: <?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></small>
      </div>
    <?php endwhile; ?>
  </div>

  <script>
    const changeBtn = document.getElementById('change-btn');
    const fileInput = document.getElementById('file-input');
    const profileImg = document.getElementById('profile-img');
    changeBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => (profileImg.src = reader.result);
      reader.readAsDataURL(file);
    });
    
    const imageUpload = document.getElementById('imageUpload');
    const preview = document.getElementById('preview');
    imageUpload.addEventListener('change', () => {
      const file = imageUpload.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    });
  </script>
</body>
</html>
