<?php
/**
 * PROJET YUMLAND - PHASE 2
 * Initialisation complète de la base de données relationnelle.
 */
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Initialisation de la base de données SQL...</h2>";

try {
    // 1. Table Utilisateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS Utilisateurs (
        id_user INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255),
        prenom VARCHAR(255),
        email VARCHAR(255) UNIQUE,
        mot_de_passe VARCHAR(255),
        role VARCHAR(50),
        tel VARCHAR(20),
        adresse TEXT,
        solde_miams INTEGER DEFAULT 0
    )");

    // 2. Table Produits (Plats et Menus)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Produits (
        id_produit INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        categorie VARCHAR(100),
        prix REAL(5,2) NOT NULL,
        image_url VARCHAR(255),
        description TEXT
    )");

    // 3. Table Commandes
    $pdo->exec("CREATE TABLE IF NOT EXISTS Commandes (
        id_commande INT AUTO_INCREMENT PRIMARY KEY,
        id_client INT,
        id_livreur INT,
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
        prix_total REAL(10,2),
        statut VARCHAR(50) DEFAULT 'En attente',
        paiement_statut VARCHAR(50) DEFAULT 'Non payé',
        cybank_transaction VARCHAR(255),
        mode_retrait VARCHAR(50) DEFAULT 'livraison',
        adresse_livraison TEXT,
        FOREIGN KEY (id_client) REFERENCES Utilisateurs(id_user),
        FOREIGN KEY (id_livreur) REFERENCES Utilisateurs(id_user)
    )");

    // 4. Table Contenu_Commandes (Liaison produits/commandes)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Contenu_Commandes (
        id_commande INT,
        id_produit INT,
        quantite INT,
        prix_unitaire REAL,
        options_choisies TEXT, -- Format JSON pour les ingrédients à enlever/ajouter
        PRIMARY KEY (id_commande, id_produit),
        FOREIGN KEY (id_commande) REFERENCES Commandes(id_commande),
        FOREIGN KEY (id_produit) REFERENCES Produits(id_produit)
    )");

    // 5. Table Paiements (Coordonnées bancaires, lien client/commande)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Paiements (
        id_paiement INT AUTO_INCREMENT PRIMARY KEY,
        id_commande INT,
        id_client INT,
        montant REAL NOT NULL,
        date_transaction DATETIME DEFAULT CURRENT_TIMESTAMP,
        cybank_transaction_id VARCHAR(255),
        carte_masquee VARCHAR(20), -- Ex: **** **** **** 1234
        FOREIGN KEY (id_commande) REFERENCES Commandes(id_commande),
        FOREIGN KEY (id_client) REFERENCES Utilisateurs(id_user)
    )");

    // 6. Table Coupons (Réductions)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Coupons (
        id_coupon INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        remise_pourcentage INT DEFAULT 0,
        remise_fixe REAL DEFAULT 0
    )");

    // 7. Table Evaluations (Noter une commande livrée)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Evaluations (
        id_eval INT AUTO_INCREMENT PRIMARY KEY,
        id_commande INT,
        note INT CHECK(note >= 1 AND note <= 5),
        commentaire TEXT,
        FOREIGN KEY (id_commande) REFERENCES Commandes(id_commande)
    )");

    echo "<li>Structure SQL complète : OK</li>";

} catch (PDOException $e) {
    die("Erreur critique : " . $e->getMessage());
}
?>