<?php
require_once __DIR__ . '/../controllers/TypePretController.php';

/**
 * GET  /typeprets   → liste des types de prêt
 * POST /typeprets   → création d’un type de prêt
 */
Flight::route('GET  /typeprets', ['TypePretController', 'getAll']);
Flight::route('POST /typeprets', ['TypePretController', 'create']);

