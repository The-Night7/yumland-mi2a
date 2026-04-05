<?php
/**
 * PROJET YUMLAND - MIGRATION JSON VERS SQL (Version Corrigée - Solution 2)
 */
require_once __DIR__ . '/includes/config.php';

try {
    echo "Démarrage de la migration...<br>";
    
    // --- SOLUTION 2 SÉCURISÉE ---
    // On utilise une approche qui préserve les données qui n'interagissent pas avec d'autres
    
    echo "Début de l'insertion avec préservation des données indépendantes...<br>";
    // --- 2. MIGRATION DES UTILISATEURS ---
    $usersJson = file_get_contents(__DIR__ . '/../data/users.json');
    $users = json_decode($usersJson, true);
    
    // On vérifie d'abord si l'utilisateur existe
    $checkUser = $pdo->prepare("SELECT id_user FROM Utilisateurs WHERE id_user = ?");
    $stmtUser = $pdo->prepare("INSERT INTO Utilisateurs (id_user, nom, prenom, email, mot_de_passe, role, tel, adresse, solde_miams) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $updateUser = $pdo->prepare("UPDATE Utilisateurs SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, role = ?, tel = ?, adresse = ?, solde_miams = ? WHERE id_user = ?");
    foreach ($users as $u) {
        $checkUser->execute([$u['id']]);
        if ($checkUser->rowCount() > 0) {
            // Mise à jour si l'utilisateur existe
            $updateUser->execute([
                $u['nom'], 
                $u['prenom'] ?? '', 
                $u['email'], 
                password_hash($u['password'], PASSWORD_DEFAULT), 
                $u['role'],
                $u['tel'] ?? '',
                $u['adresse'] ?? '',
                $u['solde_miams'] ?? 0,
                $u['id']
            ]);
        } else {
            // Insertion si l'utilisateur n'existe pas
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
    }
    echo "Utilisateurs migrés avec succès.<br>";
    
    // --- 3. Migration des Plats (Produits) ---
    $platsJson = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);
    
    $checkPlat = $pdo->prepare("SELECT id_produit FROM Produits WHERE id_produit = ?");
    $stmtPlat = $pdo->prepare("INSERT INTO Produits (id_produit, nom, categorie, prix, image_url, description) VALUES (?, ?, ?, ?, ?, ?)");
    $updatePlat = $pdo->prepare("UPDATE Produits SET nom = ?, categorie = ?, prix = ?, image_url = ?, description = ? WHERE id_produit = ?");
    
    foreach ($platsJson as $p) {
        $checkPlat->execute([$p['id']]);
        if ($checkPlat->rowCount() > 0) {
            // Mise à jour si le produit existe
            $updatePlat->execute([
                $p['nom'], 
                $p['categorie'] ?? 'plat',
                $p['prix'], 
                $p['image'], 
                $p['description'],
                $p['id']
            ]);
        } else {
            // Insertion si le produit n'existe pas
            $stmtPlat->execute([
                $p['id'], 
                $p['nom'], 
                $p['categorie'] ?? 'plat', // Valeur par défaut si non spécifiée
                $p['prix'], 
                $p['image'], 
                $p['description']
            ]);
        }
    }
    echo "Plats migrés avec succès.<br>";
    
    // --- 4. MIGRATION DES COMMANDES ---
    $commandesJson = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
    
    // Vérification et requêtes pour les commandes
    $checkCmd = $pdo->prepare("SELECT id_commande FROM Commandes WHERE id_commande = ?");
    $stmtCmd = $pdo->prepare("INSERT INTO Commandes (id_commande, id_client, id_livreur, date_commande, prix_total, statut, mode_retrait, adresse_livraison) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $updateCmd = $pdo->prepare("UPDATE Commandes SET id_client = ?, id_livreur = ?, date_commande = ?, prix_total = ?, statut = ?, mode_retrait = ?, adresse_livraison = ? WHERE id_commande = ?");
    
    // Vérification pour le contenu des commandes
    $checkContenu = $pdo->prepare("SELECT id_commande, id_produit FROM Contenu_Commandes WHERE id_commande = ? AND id_produit = ?");
    $stmtContenu = $pdo->prepare("INSERT INTO Contenu_Commandes (id_commande, id_produit, quantite, prix_unitaire, options_choisies) VALUES (?, ?, ?, ?, ?)");
    $updateContenu = $pdo->prepare("UPDATE Contenu_Commandes SET quantite = ?, prix_unitaire = ?, options_choisies = ? WHERE id_commande = ? AND id_produit = ?");
    
    foreach ($commandesJson as $c) {
        // 1. Traitement de la commande
        $checkCmd->execute([$c['id']]);
        if ($checkCmd->rowCount() > 0) {
            // Mise à jour si la commande existe
            $updateCmd->execute([
                $c['user_id'],
                $c['livreur_id'], 
                $c['date'],
                $c['montant_total'],
                $c['status'],
                $c['mode'],
                $c['adresse_livraison'],
                $c['id']
            ]);
        } else {
            // Insertion si la commande n'existe pas
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
        }
    
        // 2. Traitement du contenu de la commande
        if (isset($c['details']) && is_array($c['details'])) {
            foreach ($c['details'] as $item) {
                // On transforme le tableau d'options en chaîne JSON pour la colonne TEXT
                $optionsJson = json_encode($item['options'] ?? []);
                
                $checkContenu->execute([$c['id'], $item['plat_id']]);
                if ($checkContenu->rowCount() > 0) {
                    // Mise à jour si l'élément existe
                    $updateContenu->execute([
                        $item['quantite'],
                        $item['prix_unitaire'],
                        $optionsJson,
                        $c['id'],
                        $item['plat_id']
                    ]);
                } else {
                    // Insertion si l'élément n'existe pas
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
    }
    echo "Migration des commandes et du contenu réussie !<br>";
    
    echo "<strong>Migration terminée avec succès !</strong>";
} catch (Exception $e) {
    die("Erreur lors de la migration : " . $e->getMessage());
}
?>