<?php
require_once __DIR__ . '/../db.php';

class TypePret {
    public static function getAll() {
        return getDB()->query("SELECT * FROM type_pret")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function create($data) {
        $db = getDB();
        $db->prepare("INSERT INTO type_pret (libelle, taux) VALUES (?, ?)")
           ->execute([$data->libelle, $data->taux]);
        return $db->lastInsertId();
    }
}

