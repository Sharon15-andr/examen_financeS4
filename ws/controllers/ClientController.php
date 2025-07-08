<?php
require_once __DIR__ . '/../models/Client.php';

class ClientController {
    public static function getAll() {
        Flight::json(Client::getAll());
    }

    public static function create() {
        $id = Client::create(Flight::request()->data);
        Flight::json(['message' => 'Client ajouté', 'id' => $id]);
    }
}
