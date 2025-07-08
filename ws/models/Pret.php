<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/InteretMensuel.php';

class Pret {
    public static function getAll() {
        $sql = "SELECT p.*, c.nom, c.prenom, t.libelle, t.taux
                FROM pret p
                JOIN client c ON p.client_id = c.id
                JOIN type_pret t ON p.type_pret_id = t.id";
        return getDB()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param object $data {client_id, type_pret_id, montant, date_debut, duree_mois, assurance, delai}
     */
    public static function create($data) {
           $db = getDB();
    
    // Validation des données
    if ($data->montant <= 0 || $data->duree_mois <= 0) {
        throw new Exception("Montant et durée doivent être positifs");
    }

    // Insertion du prêt
    $stmt = $db->prepare(
        "INSERT INTO pret (client_id, type_pret_id, montant, date_debut, duree_mois, assurance, delai) 
         VALUES (:client_id, :type_pret_id, :montant, :date_debut, :duree_mois, :assurance, :delai)"
    );
    
    $stmt->execute([
        ':client_id' => $data->client_id,
        ':type_pret_id' => $data->type_pret_id,
        ':montant' => $data->montant,
        ':date_debut' => $data->date_debut,
        ':duree_mois' => $data->duree_mois,
        ':assurance' => $data->assurance,
        ':delai' => $data->delai
    ]);
        $pretId = $db->lastInsertId();

        // Récupération du taux
        $taux = $db->prepare("SELECT taux FROM type_pret WHERE id = ?");
        $taux->execute([$data->type_pret_id]);
        $tauxAnnuel = $taux->fetchColumn();

        // Calcul des intérêts mensuels
        self::calculerInteretsMensuels($pretId, $data->montant, $tauxAnnuel, $data->duree_mois, $data->assurance ?? 0, $data->delai ?? 0, $data->date_debut);

        return $pretId;
    }

    /**
     * Calcule et enregistre les intérêts mensuels
     */
    private static function calculerInteretsMensuels($pretId, $montant, $tauxAnnuel, $dureeMois, $assurance, $delai, $dateDebut) {
        $tauxMensuel = ($tauxAnnuel / 100) / 12;
        $tauxAssuranceMensuel = ($assurance / 100) / 12;
        $date = new DateTime($dateDebut);
        $reste = $montant;

        // Période de différé
        for ($i = 0; $i < $delai; $i++) {
            $interet = $reste * $tauxMensuel;
            $assuranceMensuelle = $reste * $tauxAssuranceMensuel;
            InteretMensuel::create($pretId, $date->format('n'), $date->format('Y'), $interet, $assuranceMensuelle);
            $date->modify('+1 month');
        }

        // Période de remboursement
        $mensualite = ($montant * $tauxMensuel) / (1 - pow(1 + $tauxMensuel, -$dureeMois));
        for ($i = $delai; $i < $dureeMois + $delai; $i++) {
            $interet = $reste * $tauxMensuel;
            $capital = $mensualite - $interet;
            $reste -= $capital;
            $assuranceMensuelle = $reste * $tauxAssuranceMensuel;
            InteretMensuel::create($pretId, $date->format('n'), $date->format('Y'), $interet, $assuranceMensuelle);
            $date->modify('+1 month');
        }
    }

    /**
     * Simulation d'un prêt avec annuité constante
     */
    public static function simuler($montant, $tauxAnnuel, $dureeMois, $assurance = 0, $delai = 0) {
        $tauxMensuel = ($tauxAnnuel / 100) / 12;
        $tauxAssuranceMensuel = ($assurance / 100) / 12;
        
        // Calcul de la mensualité
        $mensualite = ($montant * $tauxMensuel) / (1 - pow(1 + $tauxMensuel, -$dureeMois));
        $assuranceMensuelle = $montant * $tauxAssuranceMensuel;
        $mensualiteTotale = $mensualite + $assuranceMensuelle;

        // Tableau d'amortissement
        $reste = $montant;
        $amortissement = [];

        // Période de différé
        for ($i = 1; $i <= $delai; $i++) {
            $interets = $reste * $tauxMensuel;
            $amortissement[] = [
                'mois' => $i,
                'type' => 'différé',
                'capital' => 0,
                'interets' => round($interets, 2),
                'assurance' => round($assuranceMensuelle, 2),
                'total' => round($interets + $assuranceMensuelle, 2),
                'reste' => round($reste, 2)
            ];
        }

        // Période de remboursement
        for ($i = $delai + 1; $i <= $dureeMois + $delai; $i++) {
            $interets = $reste * $tauxMensuel;
            $capital = $mensualite - $interets;
            $reste -= $capital;
            
            $amortissement[] = [
                'mois' => $i,
                'type' => 'remboursement',
                'capital' => round($capital, 2),
                'interets' => round($interets, 2),
                'assurance' => round($assuranceMensuelle, 2),
                'total' => round($mensualite + $assuranceMensuelle, 2),
                'reste' => round(max($reste, 0), 2)
            ];
        }

        return [
            'mensualite' => round($mensualite, 2),
            'mensualite_totale' => round($mensualiteTotale, 2),
            'cout_total' => round($mensualiteTotale * $dureeMois, 2),
            'cout_interets' => round($mensualite * $dureeMois - $montant, 2),
            'cout_assurance' => round($assuranceMensuelle * ($dureeMois + $delai), 2),
            'amortissement' => $amortissement
        ];
    }
}
