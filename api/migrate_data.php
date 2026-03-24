<?php
/**
 * PROJET YUMLAND - MIGRATION JSON VERS SQL (Version Corrigée - Solution 2)
 */
require_once __DIR__ . '/includes/config.php';

try {
    echo "Démarrage de la migration...<br>";
    
    // --- SOLUTION 2 SÉCURISÉE ---
    // On utilise "DELETE FROM" au lieu de "TRUNCATE" car c'est plus souple avec les clés étrangères
    // Et on ajoute un @ pour ignorer l'erreur si la table n'existe pas encore,  
    // ou mieux, on vérifie l'ordre.
    
    $tablesToClean = ['Contenu_Commandes', 'Paiements', 'Evaluations', 'Commandes', 'Produits', 'Utilisateurs', 'Coupons'];
    
    foreach ($tablesToClean as $table) {
        try {
            $pdo->exec("DELETE FROM $table");
        } catch (PDOException $e) {
            // Si la table n'existe pas, on ignore l'erreur et on continue
            echo "Note : La table $table n'existait pas encore ou était déjà vide.<br>";
        }
    }
    
    echo "Nettoyage terminé. Début de l'insertion...<br>";
    // --- 2. MIGRATION DES UTILISATEURS ---
    $usersJson = file_get_contents(__DIR__ . '/../data/users.json');
    $users = json_decode($usersJson, true);
    $stmtUser = $pdo->prepare("INSERT INTO Utilisateurs (id_user, nom, prenom, email, mot_de_passe, role, tel, adresse, solde_miams) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        // On hache le mot de passe s'il ne l'est pas déjà (optionnel selon ton JSON)
        $stmtUser->execute([
            $u['id'], 
            $u['nom'], 
            $u['prenom'] ?? '', 
            $u['email'], 
            password_hash($u['password'], PASSWORD_DEFAULT), 
            $u['role'],
            $u['tel'] ?? '',
            $u['adresse'] ?? '',
            $u['solde_miams'] ?? 0
        ]);
    }
    echo "Utilisateurs migrés avec succès.<br>";
    
    // --- 3. Migration des Plats (Produits) ---
    $platsJson = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);

    // AJOUTEZ CETTE LIGNE CI-DESSOUS (elle manquait probablement)
    $stmtPlat = $pdo->prepare("INSERT INTO Produits (id_produit, nom, categorie, prix, image_url, description) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($platsJson as $p) {
        // C'est ici que l'erreur se produisait à la ligne 53
        $stmtPlat->execute([
            $p['id'], 
            $p['nom'], 
            $p['categorie'] ?? 'plat', // Valeur par défaut si non spécifiée
            $p['prix'], 
            $p['image'], 
            $p['description']
        ]);
    }
    echo "Plats migrés avec succès.<br>";
    
    // --- 4. MIGRATION DES COMMANDES ---
    $commandesJson = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
    
    // Requête pour la table parente
    $stmtCmd = $pdo->prepare("INSERT INTO Commandes (id_commande, id_client, id_livreur, date_commande, prix_total, statut, mode_retrait, adresse_livraison) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Requête pour votre table de liaison : Contenu_Commandes
    $stmtContenu = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES (?, ?, ?, ?, ?)");
    foreach ($commandesJson as $c) {
        // 1. Insertion dans "Commandes"
        $stmtCmd->execute([
            $c['id'],
            $c['user_id'],
            $c['livreur_id'], 
            $c['date'],
            $c['montant_total'],
            $c['status'],
            $c['mode'],
            $c['adresse_livraison']
        ]);
        
        // 2. Insertion dans "Contenu_Commandes"
        if (isset($c['details']) && is_array($c['details'])) {
            foreach ($c['details'] as $item) {
                // On transforme le tableau d'options en chaîne JSON pour la colonne TEXT
                $optionsJson = json_encode($item['options'] ?? []);
                $stmtContenu->execute([
                    $c['id'],           // id_commande
                    $item['plat_id'],   // id_produit (clé du JSON)
                    $item['quantite'],  // quantite
                    $item['prix_unitaire'], // prix_unitaire
                    $optionsJson        // options_choisies (format JSON)
                ]);
            }
        }
    }
    
    echo "Migration des commandes et du contenu réussie !<br>";
    
    echo "<strong>Migration terminée avec succès !</strong>";
} catch (Exception $e) {
    die("Erreur lors de la migration : " . $e->getMessage());
}
?>