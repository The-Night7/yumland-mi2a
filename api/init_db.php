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
        prix REAL NOT NULL,
        image_url VARCHAR(255),
        description TEXT
    )");

    // 3. Table Commandes
    $pdo->exec("CREATE TABLE IF NOT EXISTS Commandes (
        id_commande INT AUTO_INCREMENT PRIMARY KEY,
        id_client INT,
        id_livreur INT,
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
        prix_total REAL,
        statut VARCHAR(50) DEFAULT 'En attente',
        paiement_statut VARCHAR(50) DEFAULT 'Non payé',
        cybank_transaction VARCHAR(255),
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
        PRIMARY KEY (id_commande, id_produit),
        FOREIGN KEY (id_commande) REFERENCES Commandes(id_commande),
        FOREIGN KEY (id_produit) REFERENCES Produits(id_produit)
    )");

    echo "<li>Structure SQL complète : OK</li>";

} catch (PDOException $e) {
    die("Erreur critique : " . $e->getMessage());
}
?>