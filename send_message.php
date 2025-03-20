<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "message");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['receiver_id'], $_POST['message'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $stmt = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
    $stmt->execute();
}
?>
