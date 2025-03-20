<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat en Temps Réel</title>
    <script>
        let ws = new WebSocket("ws://localhost:8080/chat");

        ws.onopen = () => {
            console.log("Connecté au serveur WebSocket !");
        };

        ws.onmessage = (event) => {
            let data = JSON.parse(event.data);
            let chatBox = document.getElementById("chat-box");
            let messageDiv = document.createElement("div");
            messageDiv.classList.add("message");
            messageDiv.innerHTML = `<strong>Utilisateur ${data.sender_id} :</strong> ${data.message}`;
            chatBox.appendChild(messageDiv);
        };

        function sendMessage() {
            let messageInput = document.getElementById("message");
            let message = messageInput.value.trim();

            if (message === "") return;

            let data = {
                sender_id: <?php echo $_SESSION['user_id']; ?>,
                receiver_id: 2, // Remplace avec l'ID du destinataire
                message: message
            };

            ws.send(JSON.stringify(data));
            messageInput.value = "";
        }
    </script>
</head>
<body>
    <div class="chat-container">
        <div id="chat-box" class="chat-box"></div>
        <input type="text" id="message" placeholder="Écrire un message..." />
        <button onclick="sendMessage()">Envoyer</button>
    </div>
</body>
</html>
