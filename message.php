<?php
session_start();
include 'internet.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$receiver_id = $_GET['receiver_id'] ?? null;
if (!$receiver_id) exit("No user specified.");

// Fetch receiver info
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) exit("User not found.");

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message_text = $_POST['message_text'];
    $sender_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message_text);
    $stmt->execute();
    header("Location: message.php?receiver_id=$receiver_id");
    exit();
}

// Fetch previous messages
$stmt = $conn->prepare("SELECT m.message_text, m.sent_at, u.full_name, m.sender_id
                        FROM messages m
                        JOIN users u ON m.sender_id = u.user_id
                        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                           OR (m.sender_id = ? AND m.receiver_id = ?)
                        ORDER BY m.sent_at ASC");
$stmt->bind_param("iiii", $_SESSION['user_id'], $receiver_id, $receiver_id, $_SESSION['user_id']);
$stmt->execute();
$messages = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Chat with <?php echo htmlspecialchars($user['full_name']); ?></title>
        <style>
            body { 
                font-family: Arial; 
                padding: 20px; 
                background: #f0f2f5; 
            }
            .chat-box { 
                max-width: 600px; 
                margin: 0 auto 20px; 
                padding: 15px; 
                background: white; 
                border-radius: 10px; 
                height: 400px; 
                overflow-y: scroll; 
            }
            .chat-box p { margin: 5px 0; }
            .chat-box .you { 
                text-align: right; 
                color: blue; 
            }
            .chat-box .them { text-align: left; color: green; }
            form { max-width: 600px; margin: 0 auto; display: flex; gap: 10px; }
            input { flex: 1; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
            button a{
                text-decoration: none;
                color: #ccc;
            }
            button { 
                padding: 10px 20px; 
                border-radius: 5px; 
                border: none; 
                background: #0b2041; 
                color: white; 
                cursor: pointer; 
            }
        </style>
    </head>
    <body>
        <h2>Chat with <?php echo htmlspecialchars($user['full_name']); ?></h2>

        <div class="chat-box">
        <?php while ($msg = $messages->fetch_assoc()): ?>
            <p class="<?php echo ($msg['sender_id'] == $_SESSION['user_id']) ? 'you' : 'them'; ?>">
                <strong><?php echo ($msg['sender_id'] == $_SESSION['user_id']) ? 'You' : htmlspecialchars($msg['full_name']); ?>:</strong> 
                <?php echo htmlspecialchars($msg['message_text']); ?>
                <small>(<?php echo $msg['sent_at']; ?>)</small>
            </p>
        <?php endwhile; ?>
        </div>

        <form method="POST">
            <input type="text" name="message_text" placeholder="Type a message..." required>
            <button><a href="Dashboard.php">Back</a></button><button type="submit">Send</button>
        </form>
    </body>
</html>
