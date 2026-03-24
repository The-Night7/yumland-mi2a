<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Rediriger si déjà connecté
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

<main class="form-page" style="padding: 40px 20px;">
    <section class="form-container narrow card-style" style="max-width: 450px; margin: 0 auto; padding: 30px;">
        <h1 style="text-align: center;">Connexion</h1>
        <p style="text-align: center; margin-bottom: 20px;">Heureux de vous revoir !</p>

        <form id="login-form">
            <input type="hidden" id="csrf_token" name="csrf_token" value="<?= $csrf_token ?>">
            
            <fieldset style="border: none; padding: 0; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email" style="display: block; font-weight: bold; margin-bottom: 5px;">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="exemple@email.com" required autocomplete="username" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>

                <div class="form-group">
                    <label for="password" style="display: block; font-weight: bold; margin-bottom: 5px;">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
            </fieldset>

            <button type="button" onclick="submitLogin()" class="btn-primary" style="width: 100%; padding: 15px; font-size: 16px; font-weight: bold; cursor: pointer;">Se Connecter</button>
            <div id="login-error" style="color: #D32F2F; margin-top: 15px; display: none; text-align: center; font-weight: bold; padding: 10px; background: #ffebee; border-radius: 4px;"></div>
        </form>

        <p class="switch-form" style="text-align: center; margin-top: 25px;">Pas encore de compte ? <a href="/api/pages/inscription.php" style="font-weight: bold;">Créer un compte</a></p>
    </section>
</main>

<script>
function submitLogin() {
    const form = document.getElementById('login-form');
    const formData = new FormData(form);
    
    fetch('/api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/api/index.php'; // Redirection après connexion
        } else {
            const errorDiv = document.getElementById('login-error');
            errorDiv.innerText = data.message;
            errorDiv.style.display = 'block';
        }
    });
}
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>