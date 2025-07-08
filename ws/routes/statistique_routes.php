<?php
require_once __DIR__ . '/../controllers/StatistiqueController.php';

/**
 * GET /interets
 *   ?mois_debut=1&annee_debut=2025&mois_fin=12&annee_fin=2025
 *   → agrégation globale des intérêts EF
 *
 * GET /interets_client
 *   ?client_id=3&mois_debut=1&annee_debut=2025&mois_fin=12&annee_fin=2025
 *   → agrégation des intérêts d’un seul client
 */
Flight::route('GET /interets',         ['StatistiqueController', 'getInterets']);
Flight::route('GET /interets_client', ['StatistiqueController', 'getInteretsClient']);

