<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "message");

if ($mysqli->connect_error) {
    die("Échec de la connexion à la base de données : " . $mysqli->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; // Le mot de passe est en clair (DANGEREUX en production)

    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $stored_password);
        $stmt->fetch();

        if ($password === $stored_password) { // Comparaison directe
            // Mettre à jour le statut de l'utilisateur en "online"
            $update_status = $mysqli->prepare("UPDATE users SET statuts = 'online' WHERE id = ?");
            $update_status->bind_param("i", $id);
            $update_status->execute();

            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;

            header("Location: index.php");
            exit;
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Utilisateur introuvable.";
    }
}
?>

<form method="post">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
</form>
