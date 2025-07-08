<?php
require_once __DIR__ . '/../db.php';

class Client {
    public static function getAll() {
        return getDB()->query("SELECT * FROM client")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO client (nom, prenom) VALUES (?, ?)");
        $stmt->execute([$data->nom, $data->prenom]);
        return $db->lastInsertId();
    }
}
