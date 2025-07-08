<?php
require_once __DIR__ . '/../controllers/FondController.php';

/**
 * GET  /fonds   → liste tous les fonds
 * POST /fonds   → ajoute un fond
 */
Flight::route('GET  /fonds', ['FondController', 'getAll']);
Flight::route('POST /fonds', ['FondController', 'create']);

