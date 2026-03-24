<?php
/**
<<<<<<< HEAD
 * api/migrate_data.php
 * Migration intégrale des données JSON vers SQLite
 */
require_once __DIR__ . '/includes/config.php';

$jsonPath = __DIR__ . '/../data/plats.json'; 

if (!file_exists($jsonPath)) {
    die("Erreur : Le fichier source data/plats.json est introuvable.");
}

$plats = json_decode(file_get_contents($jsonPath), true);

try {
    // Début de la transaction pour la performance
    $pdo->beginTransaction();

    // Nettoyage de la table avant import
    $pdo->exec("DELETE FROM Produits");

    $stmt = $pdo->prepare("INSERT INTO Produits (nom, categorie, prix, image_url) VALUES (?, ?, ?, ?)");

    foreach ($plats as $plat) {
        $stmt->execute([
            $plat['nom'],
            $plat['categorie'] ?? 'Plat',
            $plat['prix'],
            $plat['image'] ?? ''
        ]);
    }

    // Validation de toutes les insertions
    $pdo->commit();

    echo "<h2>✅ Migration terminée avec succès</h2>";
    echo "<p>Nombre de produits importés : " . count($plats) . "</p>";
    echo "<p>Votre base de données est maintenant la seule source de vérité.</p>";

} catch (Exception $e) {
    // En cas d'erreur, on annule tout
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "❌ Erreur lors de la migration : " . $e->getMessage();
=======
 * PROJET YUMLAND - MIGRATION JSON VERS SQL
 * Transfère Utilisateurs, Plats et Commandes.
 */
require_once __DIR__ . '/includes/config.php';

try {
    $pdo->beginTransaction();

    // --- 1. Migration des Utilisateurs ---
    $usersJson = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
    $pdo->exec("DELETE FROM Utilisateurs");
    $stmtUser = $pdo->prepare("INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, tel, adresse) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($usersJson as $u) {
        // Sécurité : On hache le MDP si ce n'est pas déjà fait
        $mdp = (strlen($u['password']) < 20) ? password_hash($u['password'], PASSWORD_DEFAULT) : $u['password'];
        $stmtUser->execute([$u['nom'], $u['prenom'] ?? '', $u['email'], $mdp, $u['role'], $u['tel'] ?? '', $u['adresse'] ?? '']);
    }
    echo "<li>Utilisateurs migrés.</li>";

    // --- 2. Migration des Plats et Menus ---
    $platsJson = json_decode(file_get_contents(__DIR__ . '/../data/plats.json'), true);
    $pdo->exec("DELETE FROM Produits");
    $stmtPlat = $pdo->prepare("INSERT INTO Produits (nom, categorie, prix, image_url, description) VALUES (?, ?, ?, ?, ?)");
    foreach ($platsJson as $p) {
        $stmtPlat->execute([$p['nom'], $p['categorie'] ?? 'Plat', $p['prix'], $p['image'] ?? '', $p['description'] ?? '']);
    }
    echo "<li>Produits migrés.</li>";

    // --- 3. Migration des Commandes (Historique) ---
    $cmdJson = json_decode(file_get_contents(__DIR__ . '/../data/commandes.json'), true);
    $pdo->exec("DELETE FROM Commandes");
    if($cmdJson) {
        $stmtCmd = $pdo->prepare("INSERT INTO Commandes (id_commande, id_client, date_commande, prix_total, statut) VALUES (?, ?, ?, ?, ?)");
        foreach ($cmdJson as $c) {
            $stmtCmd->execute([$c['id'], $c['user_id'] ?? 1, $c['date'] ?? date('Y-m-d H:i:s'), $c['total'], $c['status']]);
        }
    }
    echo "<li>Historique des commandes migré.</li>";

    $pdo->commit();
    echo "<h3>MIGRATION TERMINÉE AVEC SUCCÈS !</h3>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Erreur migration : " . $e->getMessage());
>>>>>>> 6518598e92383c1a044d45be77e48db623dcf586
}
?>