<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "message");

$user_id = $_SESSION['user_id'];
$result = $mysqli->query("SELECT id, username, statuts FROM users WHERE id != $user_id");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chat Messenger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f5f5f5;
        }
        .user-list {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .user-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 50px;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: space-between;
            width: 200px;
        }
        .user-btn:hover {
            background: #0056b3;
        }
        .chat-box {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: center;
            cursor: pointer;
        }
        .chat-body {
            height: 250px;
            overflow-y: auto;
            padding: 10px;
            background: #fafafa;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-footer {
            padding: 10px;
            display: flex;
            gap: 5px;
        }
        .chat-footer input {
            flex: 1;
            padding: 5px;
        }
        .chat-footer button {
            background: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
.message {
    max-width: 70%;
    padding: 8px;
    border-radius: 10px;
    margin: 5px 0;
}
.message.sent {
    background: #007bff;
    color: white;
    align-self: flex-end;
}
.message.received {
    background: #e9ecef;
    color: black;
    align-self: flex-start;
}


    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center my-4">Bienvenue, <?php echo $_SESSION['user_id']; ?> !</h2>
</div>

<!-- Liste des utilisateurs -->
<div class="user-list">
    <?php while ($row = $result->fetch_assoc()): ?>
        <button class="user-btn" data-userid="<?php echo $row['id']; ?>" data-username="<?php echo $row['username']; ?>">
            <?php echo $row['username']; ?>
            <span class="status" style="color: <?php echo $row['statuts'] == 'connecté' ? 'green' : 'red'; ?>">
                <?php echo ucfirst($row['statuts']); ?>
            </span>
        </button>
    <?php endwhile; ?>
</div>

<!-- Conteneur pour les bulles de chat -->
<div id="chat-container"></div>

<script>
    var chatWindows = {};

    $(".user-btn").click(function() {
        var userId = $(this).data("userid");
        var username = $(this).data("username");

        // Vérifier si une discussion est déjà ouverte
        if (!chatWindows[userId]) {
            var chatBox = `
                <div class="chat-box" id="chat-${userId}">
                    <div class="chat-header" onclick="toggleChat(${userId})">${username} <span onclick="closeChat(${userId})" style="float:right; cursor:pointer;">✖</span></div>
                    <div class="chat-body" id="messages-${userId}"></div>
                    <div class="chat-footer">
                        <input type="text" id="message-${userId}" placeholder="Écrivez un message...">
                        <button onclick="sendMessage(${userId})">Envoyer</button>
                    </div>
                </div>`;

            $("#chat-container").append(chatBox);
            $("#chat-" + userId).css("right", (Object.keys(chatWindows).length * 310) + 20 + "px").fadeIn();
            chatWindows[userId] = true;
            loadMessages(userId);
        }
    });

    function toggleChat(userId) {
        $("#chat-" + userId + " .chat-body").slideToggle();
    }

    function closeChat(userId) {
        $("#chat-" + userId).fadeOut(function() {
            $(this).remove();
            delete chatWindows[userId];
        });
    }

    function loadMessages(userId) {
        $.post("load_messages.php", { receiver_id: userId }, function(data) {
            $("#messages-" + userId).html(data);
        });
    }

    function sendMessage(userId) {
        var msg = $("#message-" + userId).val().trim();
        if (msg !== "") {
            $.post("send_message.php", { receiver_id: userId, message: msg }, function() {
                $("#message-" + userId).val("");
                loadMessages(userId);
            });
        }
    }

    setInterval(function() {
        Object.keys(chatWindows).forEach(loadMessages);
    }, 3000);
</script>

</body>
</html>
