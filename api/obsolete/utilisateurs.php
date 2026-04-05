<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Simulation : Normalement on vérifie $_SESSION['user_role'] === 'Administrateur'

// Récupération des utilisateurs
$stmt = $pdo->query("SELECT * FROM Utilisateurs");
$utilisateurs = $stmt->fetchAll();
?>

<section class="container">
    <h1>Gestion des Utilisateurs (Administrateur)</h1>
    <p>Gérez les comptes clients, livreurs et restaurateurs de la plateforme.</p>

    <table class="menu-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Solde Miams</th>
                <th>Actions (Phase 3)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id_user']) ?></td>
                    <td><?= htmlspecialchars($user['nom'] . ' ' . ($user['prenom'] ?? '')) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['solde_miams'] ?? 0) ?></td>
                    <td>
                        <!-- Les actions "href" pointent vers des # pour l'instant (prêt pour la Phase 3) -->
                        <a href="#" class="btn-primary" style="background: var(--color-fry-gold); color: black; font-size: 0.8rem; padding: 5px;">
                            Modifier Statut
                        </a>
                        <a href="#" class="btn-primary" style="background: var(--color-coal-black); font-size: 0.8rem; padding: 5px;">
                            Bloquer
                        </a>
                        <?php if (strtolower($user['role']) === 'client'): ?>
                            <a href="#" class="btn-primary" style="font-size: 0.8rem; padding: 5px;">
                                Accorder Remise
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>