<?php
/**
<<<<<<< HEAD
 * Projet Yumland - Phase 2
 * Script d'initialisation de la base de données SQLite
 * Ce fichier définit la structure relationnelle nécessaire au fonctionnement de l'application.
 */

require_once __DIR__ . '/includes/config.php';

// Configuration de l'affichage pour le suivi de l'exécution
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Initialisation du Système de Gestion de Base de Données</h2>";

try {
    // 1. Table des Utilisateurs
    // Gère les 4 profils : Client, Administrateur, Restaurateur, Livreur
    $sql_utilisateurs = "
    CREATE TABLE IF NOT EXISTS Utilisateurs (
=======
 * PROJET YUMLAND - PHASE 2
 * Initialisation complète de la base de données relationnelle.
 */
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Initialisation de la base de données SQL...</h2>";

try {
    // 1. Table Utilisateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS Utilisateurs (
>>>>>>> 6518598e92383c1a044d45be77e48db623dcf586
        id_user INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT,
        prenom TEXT,
        email TEXT UNIQUE,
        mot_de_passe TEXT,
        role TEXT,
        tel TEXT,
        adresse TEXT,
        solde_miams INTEGER DEFAULT 0
<<<<<<< HEAD
    );
    ";
    $pdo->exec($sql_utilisateurs);
    echo "<li>Structure table 'Utilisateurs' : OK</li>";

    // 2. Initialisation du compte Administrateur par défaut
    $check_admin = $pdo->query("SELECT COUNT(*) FROM Utilisateurs WHERE email = 'admin@yumland.fr'")->fetchColumn();
    
    if ($check_admin == 0) {
        $pwd_admin = password_hash('Admin123!', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO Utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $insert->execute(['Administrateur Système', 'admin@yumland.fr', $pwd_admin, 'Administrateur']);
        echo "<li>Création du compte administrateur : OK</li>";
    }

    // 3. Table des Produits
    // Référentiel complet des produits (suppression de la contrainte CHECK pour plus de flexibilité)
    $sql_produits = "
    CREATE TABLE IF NOT EXISTS Produits (
        id_produit INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        categorie TEXT, 
        prix REAL NOT NULL,
        image_url TEXT
    );
    ";
    $pdo->exec($sql_produits);
    echo "<li>Structure table 'Produits' : OK</li>";

    // 4. Table des Commandes
    // Intègre le suivi du cycle de vie et les données de transaction CYBank
    $sql_commandes = "
    CREATE TABLE IF NOT EXISTS Commandes (
=======
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
>>>>>>> 6518598e92383c1a044d45be77e48db623dcf586
        id_commande INTEGER PRIMARY KEY AUTOINCREMENT,
        id_client INTEGER,
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
<<<<<<< HEAD
        cybank_transaction TEXT,
        paiement_statut TEXT DEFAULT 'En attente',
        FOREIGN KEY (id_client) REFERENCES Utilisateurs(id_user),
        FOREIGN KEY (id_livreur) REFERENCES Utilisateurs(id_user)
    );
    ";
    $pdo->exec($sql_commandes);
    echo "<li>Structure table 'Commandes' (incluant CYBank) : OK</li>";

    // 5. Table Contenu_Commandes
    // Table de liaison stockant le détail des produits par commande
    $sql_contenu = "
    CREATE TABLE IF NOT EXISTS Contenu_Commandes (
        id_commande INTEGER NOT NULL,
        id_produit INTEGER NOT NULL,
        quantite INTEGER NOT NULL,
        prix_unitaire REAL NOT NULL,
        PRIMARY KEY (id_commande, id_produit),
        FOREIGN KEY (id_commande) REFERENCES Commandes(id_commande),
        FOREIGN KEY (id_produit) REFERENCES Produits(id_produit)
    );
    ";
    $pdo->exec($sql_contenu);
    echo "<li>Structure table 'Contenu_Commandes' : OK</li>";

    echo "<h3>Schéma de base de données initialisé avec succès.</h3>";

} catch (PDOException $e) {
    // Journalisation de l'erreur en cas d'échec
    error_log("Erreur SQL lors de l'initialisation : " . $e->getMessage());
    echo "<p style='color:red;'>Erreur système : L'initialisation a échoué. Consultez les logs serveur.</p>";
=======
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
>>>>>>> 6518598e92383c1a044d45be77e48db623dcf586
}
?>