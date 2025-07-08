<?php
require_once __DIR__ . '/../models/Fond.php';

class FondController {
    public static function getAll()     { Flight::json(Fond::getAll()); }
    public static function create() {
        $id = Fond::create(Flight::request()->data);
        Flight::json(['message' => 'Fond ajouté', 'id' => $id]);
    }
}

