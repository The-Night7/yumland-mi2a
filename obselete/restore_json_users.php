<?php
require_once __DIR__ . '/includes/config.php';

try {
    // 1. On vide la base de données (commandes et utilisateurs)
    $pdo->exec("DELETE FROM Contenu_Commandes");
    $pdo->exec("DELETE FROM Paiements");
    $pdo->exec("DELETE FROM Evaluations");
    $pdo->exec("DELETE FROM Commandes");
    $pdo->exec("DELETE FROM Utilisateurs");

    // 2. Les utilisateurs de l'ancien fichier data/users.json
    $users = [
        ['nom' => 'Dupont', 'prenom' => 'Jean', 'email' => 'client1@example.com', 'role' => 'Client', 'tel' => '0123456789', 'adresse' => '12 rue des Lilas, 95000 Cergy'],
        ['nom' => 'Martin', 'prenom' => 'Sophie', 'email' => 'client2@example.com', 'role' => 'Client', 'tel' => '0234567891', 'adresse' => '24 avenue des Roses, 95800 Cergy'],
        ['nom' => 'Petit', 'prenom' => 'Marie', 'email' => 'client3@example.com', 'role' => 'Client', 'tel' => '0345678912', 'adresse' => '8 boulevard des Chênes, 95000 Cergy'],
        ['nom' => 'Dubois', 'prenom' => 'Pierre', 'email' => 'client4@example.com', 'role' => 'Client', 'tel' => '0456789123', 'adresse' => '15 rue de la Paix, 95610 Eragny'],
        ['nom' => 'Leroy', 'prenom' => 'Julie', 'email' => 'client5@example.com', 'role' => 'Client', 'tel' => '0567891234', 'adresse' => '3 allée des Pins, 95280 Jouy-le-Moutier'],
        ['nom' => 'Admin', 'prenom' => 'Principal', 'email' => 'admin1@grandmiam.com', 'role' => 'Administrateur', 'tel' => '0678912345', 'adresse' => ''],
        ['nom' => 'Admin', 'prenom' => 'Secondaire', 'email' => 'admin2@grandmiam.com', 'role' => 'Administrateur', 'tel' => '0789123456', 'adresse' => ''],
        ['nom' => 'Chef', 'prenom' => 'Principal', 'email' => 'resto@grandmiam.com', 'role' => 'Restaurateur', 'tel' => '0891234567', 'adresse' => ''],
        ['nom' => 'Express', 'prenom' => 'Jean', 'email' => 'livreur1@grandmiam.com', 'role' => 'Livreur', 'tel' => '0912345678', 'adresse' => '']
    ];

    // Le mot de passe original dans le JSON était "password".
    // Nous le recréons proprement avec bcrypt pour le nouveau système.
    $stmt = $pdo->prepare("INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, tel, adresse) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $pass = password_hash('password', PASSWORD_DEFAULT);

    foreach ($users as $u) {
        $stmt->execute([$u['nom'], $u['prenom'], $u['email'], $pass, $u['role'], $u['tel'], $u['adresse']]);
    }

    echo "<h2>✅ Succès !</h2><p>Les 9 utilisateurs du fichier JSON ont été recréés.</p><p>Leur mot de passe à tous est : <strong>password</strong></p>";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>