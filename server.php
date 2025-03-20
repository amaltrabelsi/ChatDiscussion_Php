<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nouvelle connexion : ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if ($data && isset($data['sender_id'], $data['receiver_id'], $data['message'])) {
            // Envoyer le message à tous les clients connectés
            foreach ($this->clients as $client) {
                if ($client !== $from) {
                    $client->send(json_encode($data));
                }
            }

            // Enregistrer le message dans la base de données
            $this->saveMessage($data['sender_id'], $data['receiver_id'], $data['message']);
        }
    }

    private function saveMessage($sender_id, $receiver_id, $message) {
        $mysqli = new mysqli("localhost", "root", "", "message");
        $stmt = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connexion fermée : ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erreur : {$e->getMessage()}\n";
        $conn->close();
    }
}

// Lancer le serveur WebSocket
$server = new Ratchet\App("localhost", 8080, "0.0.0.0");
$server->route('/chat', new ChatServer, ['*']);
$server->run();
