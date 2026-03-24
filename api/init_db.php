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
        id_user INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT,
        prenom TEXT,
        email TEXT UNIQUE,
        mot_de_passe TEXT,
        role TEXT,
        tel TEXT,
        adresse TEXT,
        solde_miams INTEGER DEFAULT 0
    )");

    // 2. Table Produits (Plats et Menus)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Produits (
        id_produit INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        categorie TEXT,
        prix REAL NOT NULL,
        image_url TEXT,
        description TEXT
    )");

    // 3. Table Commandes
    $pdo->exec("CREATE TABLE IF NOT EXISTS Commandes (
        id_commande INTEGER PRIMARY KEY AUTOINCREMENT,
        id_client INTEGER,
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
        prix_total REAL,
        statut TEXT DEFAULT 'En attente',
        paiement_statut TEXT DEFAULT 'Non payé',
        cybank_transaction TEXT,
        adresse_livraison TEXT,
        FOREIGN KEY (id_client) REFERENCES Utilisateurs(id_user)
    )");

    // 4. Table Contenu_Commandes (Liaison produits/commandes)
    $pdo->exec("CREATE TABLE IF NOT EXISTS Contenu_Commandes (
        id_commande INTEGER,
        id_produit INTEGER,
        quantite INTEGER,
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