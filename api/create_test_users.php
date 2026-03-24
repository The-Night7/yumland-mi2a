<?php
require_once __DIR__ . '/includes/config.php';

// Liste des comptes de test officiels de votre application
$test_users = [
    ['nom' => 'Client Test', 'email' => 'client@yumland.com', 'pass' => '123', 'role' => 'Client'],
    ['nom' => 'Admin Test', 'email' => 'admin@yumland.com', 'pass' => 'admin', 'role' => 'Administrateur'],
    ['nom' => 'Chef Test', 'email' => 'chef@yumland.com', 'pass' => 'chef', 'role' => 'Restaurateur'],
    ['nom' => 'Livreur Test', 'email' => 'livreur@yumland.com', 'pass' => 'go', 'role' => 'Livreur'],
];

echo "<h2>Création / Mise à jour des comptes de test...</h2>";

foreach ($test_users as $u) {
    // On vérifie si l'utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id_user FROM Utilisateurs WHERE email = ?");
    $stmt->execute([$u['email']]);
    $exists = $stmt->fetch();

    $hashed_password = password_hash($u['pass'], PASSWORD_DEFAULT);

    if (!$exists) {
        // Il n'existe pas, on le crée
        $stmtInsert = $pdo->prepare("INSERT INTO Utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$u['nom'], $u['email'], $hashed_password, $u['role']]);
        echo "<p>✅ Utilisateur <strong>{$u['email']}</strong> créé avec succès.</p>";
    } else {
        // Il existe déjà, on répare son mot de passe
        $stmtUpdate = $pdo->prepare("UPDATE Utilisateurs SET mot_de_passe = ?, role = ? WHERE email = ?");
        $stmtUpdate->execute([$hashed_password, $u['role'], $u['email']]);
        echo "<p>🔄 Utilisateur <strong>{$u['email']}</strong> mis à jour (mot de passe réparé).</p>";
    }
}

echo "<h3>Terminé ! Vous pouvez maintenant vous connecter.</h3>";
?>
