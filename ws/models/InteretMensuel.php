<?php
require_once __DIR__ . '/../db.php';

class InteretMensuel {
    /**
     * Crée un enregistrement d'intérêt mensuel
     * @param int $pretId - ID du prêt
     * @param int $mois - Mois (1-12)
     * @param int $annee - Année
     * @param float $interet - Montant des intérêts
     * @param float $assurance - Montant de l'assurance
     * @param float $capital - Part du capital remboursé (optionnel)
     */
    public static function create($pretId, $mois, $annee, $interet, $assurance = 0, $capital = 0) {
        $db = getDB();
        $stmt = $db->prepare(
            "INSERT INTO interet_mensuel 
            (pret_id, mois, annee, interet, assurance, capital, date_creation) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$pretId, $mois, $annee, $interet, $assurance, $capital]);
    }

    /**
     * Récupère le total des intérêts par mois pour l'établissement financier
     * @param int $moisDebut - Mois de début (1-12)
     * @param int $anneeDebut - Année de début
     * @param int $moisFin - Mois de fin (1-12)
     * @param int $anneeFin - Année de fin
     * @return array - Tableau des résultats
     */
    public static function getSumByMonth($moisDebut, $anneeDebut, $moisFin, $anneeFin) {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT 
                annee, 
                mois, 
                SUM(interet) AS total_interets,
                SUM(assurance) AS total_assurance,
                SUM(capital) AS total_capital
            FROM interet_mensuel
            WHERE (annee > :ad OR (annee = :ad AND mois >= :md))
              AND (annee < :af OR (annee = :af AND mois <= :mf))
            GROUP BY annee, mois
            ORDER BY annee, mois"
        );
        $stmt->execute([
            ':ad' => $anneeDebut, ':md' => $moisDebut,
            ':af' => $anneeFin,   ':mf' => $moisFin
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les intérêts mensuels pour un client spécifique
     * @param int $clientId - ID du client
     * @param int $moisDebut - Mois de début (1-12)
     * @param int $anneeDebut - Année de début
     * @param int $moisFin - Mois de fin (1-12)
     * @param int $anneeFin - Année de fin
     * @return array - Tableau des résultats
     */
    public static function getByClient($clientId, $moisDebut, $anneeDebut, $moisFin, $anneeFin) {
        $db = getDB();
        $sql = "
            SELECT 
                im.annee, 
                im.mois, 
                SUM(im.interet) AS total_interets,
                SUM(im.assurance) AS total_assurance,
                SUM(im.capital) AS total_capital,
                p.id AS pret_id,
                p.montant AS pret_montant
            FROM interet_mensuel im
            JOIN pret p ON im.pret_id = p.id
            WHERE p.client_id = :cid
              AND (im.annee > :ad OR (im.annee = :ad AND im.mois >= :md))
              AND (im.annee < :af OR (im.annee = :af AND im.mois <= :mf))
            GROUP BY im.annee, im.mois, p.id
            ORDER BY im.annee, im.mois";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':cid' => $clientId,
            ':ad'  => $anneeDebut, ':md' => $moisDebut,
            ':af'  => $anneeFin,   ':mf' => $moisFin
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le détail des intérêts pour un prêt spécifique
     * @param int $pretId - ID du prêt
     * @return array - Tableau des échéances
     */
    public static function getByPret($pretId) {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT 
                id,
                mois,
                annee,
                interet,
                assurance,
                capital,
                (interet + assurance + capital) AS total,
                date_creation
            FROM interet_mensuel
            WHERE pret_id = ?
            ORDER BY annee, mois"
        );
        $stmt->execute([$pretId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Génère les intérêts mensuels pour un nouveau prêt
     * @param int $pretId - ID du prêt
     * @param float $montant - Montant du prêt
     * @param float $tauxAnnuel - Taux annuel en %
     * @param int $dureeMois - Durée en mois
     * @param float $assuranceAnnuel - Taux d'assurance annuel en %
     * @param int $delai - Délai avant premier remboursement (mois)
     * @param string $dateDebut - Date de début (YYYY-MM-DD)
     */
    public static function genererPlanRemboursement($pretId, $montant, $tauxAnnuel, $dureeMois, $assuranceAnnuel = 0, $delai = 0, $dateDebut) {
        $db = getDB();
        $tauxMensuel = ($tauxAnnuel / 100) / 12;
        $assuranceMensuelle = ($assuranceAnnuel / 100) / 12 * $montant;
        $date = new DateTime($dateDebut);
        $reste = $montant;

        // Calcul de la mensualité (annuité constante)
        $mensualite = ($montant * $tauxMensuel) / (1 - pow(1 + $tauxMensuel, -$dureeMois));

        // Période de différé (intérêts seulement)
        for ($i = 0; $i < $delai; $i++) {
            $interet = $reste * $tauxMensuel;
            self::create($pretId, $date->format('n'), $date->format('Y'), $interet, $assuranceMensuelle, 0);
            $date->modify('+1 month');
        }

        // Période de remboursement
        for ($i = 0; $i < $dureeMois; $i++) {
            $interet = $reste * $tauxMensuel;
            $capital = $mensualite - $interet;
            $reste -= $capital;
            
            self::create(
                $pretId, 
                $date->format('n'), 
                $date->format('Y'), 
                $interet, 
                $assuranceMensuelle, 
                $capital
            );
            
            $date->modify('+1 month');
        }
    }
}
