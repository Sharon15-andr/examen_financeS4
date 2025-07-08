CREATE DATABASE exam CHARACTER SET utf8mb4;

USE exam;

CREATE TABLE etablissement_fonds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    montant DOUBLE NOT NULL,
    date_ajout DATE NOT NULL
);

CREATE TABLE type_pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL,
    taux DOUBLE NOT NULL  -- pourcentage annuel
);

CREATE TABLE client (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100)
);

CREATE TABLE pret (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT,
    type_pret_id INT,
    montant DOUBLE,
    date_debut DATE,
    duree_mois INT,
    FOREIGN KEY (client_id) REFERENCES client(id),
    FOREIGN KEY (type_pret_id) REFERENCES type_pret(id)
);

CREATE TABLE interet_mensuel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pret_id INT NOT NULL,
    mois TINYINT NOT NULL CHECK (mois BETWEEN 1 AND 12),
    annee SMALLINT NOT NULL,
    interet DECIMAL(10,2) NOT NULL,
    assurance DECIMAL(10,2) DEFAULT 0,
    capital DECIMAL(10,2) DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pret_id) REFERENCES pret(id) ON DELETE CASCADE
);

CREATE OR REPLACE VIEW v_interet_client_mois AS
SELECT
    c.id            AS client_id,
    CONCAT(c.prenom, ' ', c.nom) AS client,
    im.annee,
    im.mois,
    SUM(im.interet) AS total_interet
FROM interet_mensuel im
JOIN pret p   ON im.pret_id = p.id
JOIN client c ON p.client_id = c.id
GROUP BY c.id, im.annee, im.mois;

-- 2 colonnes ajoutées
ALTER TABLE pret
  ADD taux_assurance DOUBLE DEFAULT 0,        -- % annuel facultatif
  ADD mensualite    DOUBLE NULL;              -- calculée à la création

-- nouvelle table détaillant chaque échéance
CREATE TABLE IF NOT EXISTS remboursement (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pret_id INT,
  no_mois INT,           -- 1 … n
  annee   INT,
  mois    INT,
  capital_restant   DOUBLE,
  amortissement     DOUBLE,
  interet           DOUBLE,
  assurance         DOUBLE,
  mensualite_totale DOUBLE,
  FOREIGN KEY (pret_id) REFERENCES pret(id)
);
