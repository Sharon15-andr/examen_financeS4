<?php
require_once __DIR__ . '/../controllers/PretController.php';

/**
 * GET  /prets → Liste de tous les prêts
 * POST /prets → Création d'un prêt
 * POST /prets/simuler → Simulation d'un prêt
 * GET  /prets/@id/pdf → Génération du PDF
 */
Flight::route('GET  /prets', ['PretController', 'getAll']);
Flight::route('POST /prets', ['PretController', 'create']);
Flight::route('POST /prets/simuler', ['PretController', 'simuler']);
Flight::route('GET  /prets/@id/pdf', ['PretController', 'genererPDF']);
