<?php
require_once __DIR__ . '/../models/InteretMensuel.php';

class StatistiqueController {
    /**
     * GET /interets?mois_debut=1&annee_debut=2025&mois_fin=6&annee_fin=2025
     */
    public static function getInterets() {
        $r = Flight::request()->query;
        $data = InteretMensuel::getSumByMonth(
            intval($r['mois_debut']), intval($r['annee_debut']),
            intval($r['mois_fin']),   intval($r['annee_fin'])
        );
        Flight::json($data);
    }
public static function getInteretsClient() {
    $q = Flight::request()->query;
    $data = InteretMensuel::getByClient(
        intval($q['client_id']),
        intval($q['mois_debut']),  intval($q['annee_debut']),
        intval($q['mois_fin']),    intval($q['annee_fin'])
    );
    Flight::json($data);
}
}
