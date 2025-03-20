<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "message");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['receiver_id'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];

    $stmt = $mysqli->prepare("
        SELECT sender_id, message, timestamp 
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY timestamp ASC
    ");
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $class = ($row['sender_id'] == $sender_id) ? 'style="color: blue;"' : 'style="color: green;"';
        echo "<p $class><strong>" . ($row['sender_id'] == $sender_id ? "Vous" : "Lui") . ":</strong> " . $row['message'] . "</p>";
    }
}
?>
