<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    // Redirection selon le rôle
    switch ($_SESSION['user_role']) {
        case 'admin':
            redirect('/admin/dashboard.php');
            break;
        case 'restaurateur':
            redirect('/restaurateur/commandes.php');
            break;
        case 'livreur':
            redirect('/livreur/livraisons.php');
            break;
        default:
            redirect('/client/profil.php');
            break;
    }
}

$error = '';
$success = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Erreur de sécurité, veuillez réessayer.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            $user = authenticateUser($username, $password);
            
            if ($user) {
                // Redirection selon le rôle
                switch ($user['role']) {
                    case 'admin':
                        redirect('/admin/dashboard.php');
                        break;
                    case 'restaurateur':
                        redirect('/restaurateur/commandes.php');
                        break;
                    case 'livreur':
                        redirect('/livreur/livraisons.php');
                        break;
                    default:
                        redirect('/client/profil.php');
                        break;
                }
            } else {
                $error = 'Identifiants incorrects ou compte désactivé.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'connexion';
$pageTitle = 'Connexion';

// Inclure le header
include_once __DIR__ . '/../../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <h2>Connexion</h2>
            
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
            
            <form action="/public/html/connexion.php" method="post" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-primary">Se connecter</button>
            </form>
            
            <div class="auth-links">
                <p>Pas encore de compte ? <a href="/public/html/inscription.php">S'inscrire</a></p>
            </div>
        </div>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../../includes/footer.php';
?>