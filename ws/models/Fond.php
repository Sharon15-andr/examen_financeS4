<?php
require_once __DIR__ . '/../db.php';

class Fond {
    public static function getAll() {
        $db = getDB();
        return $db->query("SELECT * FROM etablissement_fonds ORDER BY date_ajout DESC")
                  ->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO etablissement_fonds (montant, date_ajout) VALUES (?, ?)"
        );
        $stmt->execute([$data->montant, $data->date_ajout]);
        return $db->lastInsertId();
    }
}

