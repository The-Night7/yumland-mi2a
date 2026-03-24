<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Rediriger si l'utilisateur est déjà connecté
if (isLoggedIn()) {
    redirect('/api/index.php');
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'connexion';
$pageTitle = 'Connexion';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container" style="max-width: 500px; margin: 0 auto;">
            <h2>Connexion</h2>
            
            <div id="login-error" class="alert alert-danger" style="display: none;"></div>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'must_login'): ?>
                <div class="alert alert-info" style="margin-bottom: 20px; background-color: var(--color-fry-gold); color: var(--color-coal-black); border: none;">
                    ⚠️ <strong>Accès requis :</strong> Vous devez vous connecter ou créer un compte pour valider votre panier et procéder au paiement.
                </div>
            <?php endif; ?>
            
            <form id="loginForm" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">Se connecter</button>
            </form>
            
            <div class="auth-links" style="text-align: center; margin-top: 15px;">
                <p>Pas encore de compte ? <a href="/api/pages/inscription.php">S'inscrire</a></p>
            </div>

            <!-- ZONE DE TEST RAPIDE -->
            <div class="test-accounts card-style" style="margin-top: 30px; padding: 15px; background: var(--color-sauce-cream); border-left: 4px solid var(--color-primary);">
                <h3 style="font-size: 1.1rem; margin-bottom: 10px; color: var(--color-coal-black);">🧪 Accès Rapides (Test)</h3>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 15px;">Cliquez sur un profil pour auto-remplir les identifiants :</p>
                
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem; background: #006064;" onclick="fillLogin('client@yumland.com', '123')">👤 Client</button>
                    <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem; background: #880e4f;" onclick="fillLogin('admin@yumland.com', 'admin')">🛡️ Admin</button>
                    <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem; background: #e65100;" onclick="fillLogin('chef@yumland.com', 'chef')">👨‍🍳 Chef</button>
                    <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 0.85rem; background: #2e7d32;" onclick="fillLogin('livreur@yumland.com', 'go')">🛵 Livreur</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Fonction pour remplir automatiquement le formulaire
function fillLogin(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}

// Interception du formulaire pour utiliser l'API JSON de login.php
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Empêcher le rechargement de la page
    
    const formData = new FormData(this);
    const errorDiv = document.getElementById('login-error');
    
    fetch('/api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirection intelligente en fonction du rôle
            switch(data.role) {
                case 'Administrateur':
                    window.location.href = '/api/admin/dashboard.php';
                    break;
                case 'Restaurateur':
                    window.location.href = '/api/restaurateur/commandes.php';
                    break;
                case 'Livreur':
                    window.location.href = '/api/livreur/livraisons.php';
                    break;
                default:
                    window.location.href = '/api/index.php';
                    break;
            }
        } else {
            errorDiv.style.display = 'block';
            errorDiv.textContent = data.message;
        }
    })
    .catch(err => {
        console.error(err);
        errorDiv.style.display = 'block';
        errorDiv.textContent = "Erreur de connexion au serveur.";
    });
});
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>
