<?php
// api/init_db.php

// 1. On appelle le "Chef Cuisinier" (notre fichier de configuration)
require_once __DIR__ . '/includes/config.php';

echo "<h1>⚙️ Initialisation de la base de données Yumland...</h1>";

try {
    // 2. On écrit la recette (la requête SQL) pour créer la table Utilisateurs
    $sql_utilisateurs = "
    CREATE TABLE IF NOT EXISTS Utilisateurs (
        id_user INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        mot_de_passe TEXT NOT NULL,
        role TEXT NOT NULL CHECK(role IN ('Client', 'Administrateur', 'Restaurateur', 'Livreur')),
        solde_miams INTEGER DEFAULT 0
    );
    ";
    
    // On exécute la requête
    $pdo->exec($sql_utilisateurs);
    echo "<p>✅ Table 'Utilisateurs' prête !</p>";

    // 3. (Bonus) On crée immédiatement un compte Administrateur de test
    // On vérifie d'abord s'il n'existe pas déjà pour éviter les doublons
    $check_admin = $pdo->query("SELECT COUNT(*) FROM Utilisateurs WHERE email = 'admin@yumland.fr'")->fetchColumn();
    
    if ($check_admin == 0) {
        // Règle de sécurité (Phase 4) : On hache TOUJOURS les mots de passe !
        $mot_de_passe_hache = password_hash('Admin123!', PASSWORD_DEFAULT);
        
        $insert = $pdo->prepare("INSERT INTO Utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $insert->execute(['Chef Admin', 'admin@yumland.fr', $mot_de_passe_hache, 'Administrateur']);
        
        echo "<p>✅ Compte Administrateur créé avec succès !</p>";
        echo "<ul><li><b>Email:</b> admin@yumland.fr</li><li><b>Mot de passe:</b> Admin123!</li></ul>";
    } else {
        echo "<p>ℹ️ Le compte Administrateur existe déjà, on ne le recrée pas.</p>";
    }

    // 4. Création de la table Commandes
    $sql_commandes = "
    CREATE TABLE IF NOT EXISTS Commandes (
        id_commande INTEGER PRIMARY KEY AUTOINCREMENT,
        id_client INTEGER NOT NULL,
        id_livreur INTEGER, 
        statut TEXT DEFAULT 'En attente' CHECK(statut IN ('En attente', 'En préparation', 'En livraison', 'Livrée')),
        prix_total REAL NOT NULL,
        adresse_livraison TEXT NOT NULL,
        code_interphone TEXT,
        date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_client) REFERENCES Utilisateurs(id_user),
        FOREIGN KEY (id_livreur) REFERENCES Utilisateurs(id_user)
    );
    ";
    
    $pdo->exec($sql_commandes);
    echo "<p>✅ Table 'Commandes' prête !</p>";


} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Erreur SQL : " . $e->getMessage() . "</p>";
}
?>