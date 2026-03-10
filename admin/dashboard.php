<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('/public/html/connexion.php');
}

// Récupérer tous les utilisateurs
$users = loadData(USERS_FILE);

// Définir la page courante pour le menu actif
$currentPage = 'admin_dashboard';
$pageTitle = 'Administration';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="admin-section">
    <div class="container">
        <h1>Tableau de bord administrateur</h1>
        
        <div class="admin-container">
            <div class="admin-sidebar">
                <div class="admin-menu card-style">
                    <h3>Menu Admin</h3>
                    <ul>
                        <li class="active"><a href="/admin/dashboard.php">Utilisateurs</a></li>
                        <li><a href="/admin/commandes.php">Commandes</a></li>
                        <li><a href="/admin/plats.php">Plats</a></li>
                        <li><a href="/admin/menus.php">Menus</a></li>
                        <li><a href="/logout.php">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="admin-content">
                <div class="admin-header">
                    <h2>Gestion des utilisateurs</h2>
                    <p>Vous pouvez consulter et gérer tous les utilisateurs de la plateforme.</p>
                </div>
                
                <div class="admin-table-container card-style">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom d'utilisateur</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['nom']) ?></td>
                                    <td><?= htmlspecialchars($user['prenom']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="role-badge role-<?= strtolower($user['role']) ?>">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($user['status']) ?>">
                                            <?= htmlspecialchars($user['status']) ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <!-- Ces boutons sont visuels uniquement pour la Phase 2 -->
                                        <button class="btn-edit" title="Modifier" disabled>✏️</button>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <button class="btn-block" title="Bloquer" disabled>🚫</button>
                                        <?php else: ?>
                                            <button class="btn-activate" title="Activer" disabled>✅</button>
                                        <?php endif; ?>
                                        <button class="btn-delete" title="Supprimer" disabled>🗑️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-note">
                    <p>Note: Les fonctionnalités de modification, blocage et suppression seront disponibles dans la Phase 3.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>