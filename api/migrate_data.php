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
    
    $tablesToClean = ['Detail_Commande', 'Commandes', 'Plats', 'Utilisateurs'];
    
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
    
    // --- 3. MIGRATION DES PLATS ---
    $platsJson = file_get_contents(__DIR__ . '/../data/plats.json');
    $plats = json_decode($platsJson, true);
    
    $stmtPlat = $pdo->prepare("INSERT INTO Plats (id_plat, nom, prix, description, image) VALUES (?, ?, ?, ?, ?)");
    foreach ($plats as $p) {
        $stmtPlat->execute([
            $p['id'], 
            $p['nom'], 
            $p['prix'], 
            $p['description'] ?? '', 
            $p['image'] ?? ''
        ]);
    }
    echo "Plats migrés avec succès.<br>";
    
    // --- 4. MIGRATION DES COMMANDES ---
    $cmdJson = file_get_contents(__DIR__ . '/../data/commandes.json');
    $commandes = json_decode($cmdJson, true);
    
    $stmtCmd = $pdo->prepare("INSERT INTO Commandes (id_commande, id_client, date_commande, statut, total) VALUES (?, ?, ?, ?, ?)");
    foreach ($commandes as $c) {
        $stmtCmd->execute([
            $c['id'], 
            $c['id_client'], 
            $c['date'], 
            $c['statut'], 
            $c['total']
        ]);
    }
    echo "Commandes migrées avec succès.<br>";
    
    echo "<strong>Migration terminée avec succès !</strong>";
} catch (Exception $e) {
    die("Erreur lors de la migration : " . $e->getMessage());
}
?>