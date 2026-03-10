<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Vérifier si l'utilisateur est connecté et est un client
if (!isLoggedIn() || !hasRole('client')) {
    redirect('/public/html/connexion.php');
}

// Récupérer les informations de l'utilisateur
$user = getUserById($_SESSION['user_id']);

$error = '';
$success = '';

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Erreur de sécurité, veuillez réessayer.';
    } else {
        // Cette fonctionnalité sera implémentée dans la Phase 3
        $success = 'La mise à jour du profil sera disponible dans la prochaine version.';
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'client_profil';
$pageTitle = 'Mon Profil';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="client-section">
    <div class="container">
        <h1>Mon Profil</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-menu card-style">
                    <h3>Menu Client</h3>
                    <ul>
                        <li class="active"><a href="/api/client/profil.php">Mon Profil</a></li>
                        <li><a href="/api/client/commandes.php">Mes Commandes</a></li>
                        <li><a href="/api/logout.php">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="profile-content card-style">
                <h2>Informations personnelles</h2>
                
                <form action="/api/client/profil.php" method="post" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse</label>
                        <textarea id="adresse" name="adresse" rows="2" disabled><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" disabled>
                    </div>
                    
                    <p class="form-note">La modification du profil sera disponible dans la prochaine version.</p>
                    
                    <button type="submit" class="btn-primary" disabled>Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>