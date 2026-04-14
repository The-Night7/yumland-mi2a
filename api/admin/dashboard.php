<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isLoggedIn() || !hasRole('Administrateur')) {
    redirect('/api/pages/connexion.php');
}

// Récupérer tous les utilisateurs
$stmt = $pdo->query("SELECT * FROM Utilisateurs");
$users = $stmt->fetchAll();

// Définir la page courante pour le menu actif
$currentPage = 'admin_dashboard';
$pageTitle = 'Administration';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<script defer>
async function toggleBlock(userId, btn) {
  const response = await fetch('/api/admin/toggle_block.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id_user: userId })
  });
  const data = await response.json();
  if (data.success) {
    const badge = document.querySelector(`#statut-${userId}`);
    if (badge) badge.textContent = data.new_statut;
    btn.textContent = data.new_statut === 'Bloqué' ? '🔓 Débloquer' : '🔒 Bloquer';
    btn.classList.toggle('btn-danger', data.new_statut === 'Bloqué');
  } else {
    alert(data.message);
  }
}
</script>

<section class="admin-section">
    <div class="container">
        <h1>Tableau de bord administrateur</h1>
        
        <div class="admin-container">
            <div class="admin-sidebar">
                <div class="admin-menu card-style">
                    <h3>Menu Admin</h3>
                    <ul>
                        <li class="active"><a href="/api/admin/dashboard.php">Utilisateurs</a></li>
                        <li><a href="/api/admin/commandes.php">Commandes</a></li>
                        <li><a href="/api/admin/plats.php">Plats</a></li>
                        <li><a href="/api/admin/menus.php">Menus</a></li>
                        <li><a href="/api/logout.php">Déconnexion</a></li>
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
                                    <td><?= $user['id_user'] ?></td>
                                    <td><?= htmlspecialchars($user['nom'] . ' ' . ($user['prenom'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($user['nom']) ?></td>
                                    <td><?= htmlspecialchars($user['prenom'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="role-badge role-<?= strtolower($user['role']) ?>">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-active">
                                            Actif
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <!-- Ces boutons sont visuels uniquement pour la Phase 2 -->
                                        <button class="btn-edit" title="Modifier" disabled>✏️</button>
                                        <?php if (true): // En attendant la gestion du statut SQL ?>
                                            <span id="statut-<?= $user['id_user'] ?>" class="status-badge">
                                                <?= htmlspecialchars($user['statut'] ?? 'Actif') ?>
                                            </span>

                                            // Dans la colonne Actions :
                                            <button 
                                                onclick="toggleBlock(<?= $user['id_user'] ?>, this)"
                                                class="<?= ($user['statut'] ?? '') === 'Bloqué' ? 'btn-activate' : 'btn-block' ?>">
                                                <?= ($user['statut'] ?? '') === 'Bloqué' ? '🔓 Débloquer' : '🔒 Bloquer' ?>
                                            </button>
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