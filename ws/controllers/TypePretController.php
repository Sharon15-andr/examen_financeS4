<?php
require_once __DIR__ . '/../models/TypePret.php';

class TypePretController {
    public static function getAll()     { Flight::json(TypePret::getAll()); }
    public static function create() {
        $id = TypePret::create(Flight::request()->data);
        Flight::json(['message' => 'Type de prêt ajouté', 'id' => $id]);
    }
}

