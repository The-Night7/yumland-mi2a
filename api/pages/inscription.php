<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('/api/index.php');
}

$error = '';
$success = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Erreur de sécurité, veuillez réessayer.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        
        // Validation des champs
        if (empty($password) || empty($confirm_password) || empty($email) || empty($nom) || empty($prenom)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } elseif (strlen($password) < 8) {
            $error = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Veuillez entrer une adresse email valide.';
        } else {
            // Préparer les données pour la fonction registerUser
            $userData = [
                'password' => $password,
                'email' => $email,
                'nom' => $nom,
                'prenom' => $prenom,
                'adresse' => $adresse,
                'telephone' => $telephone
            ];
            
            // Appel à la base de données
            $result = registerUser($userData);
            
            if ($result) {
                $success = 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.';
            } else {
                $error = 'Cette adresse email est déjà utilisée. Veuillez en choisir une autre ou vous connecter.';
            }
        }
    }
}

// Générer un token CSRF
$csrf_token = generateCSRFToken();

// Définir la page courante pour le menu actif
$currentPage = 'inscription';
$pageTitle = 'Inscription';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <h2>Créer un compte</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="color: red; padding: 10px; border: 1px solid red; margin-bottom: 15px;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="color: green; padding: 10px; border: 1px solid green; margin-bottom: 15px;">
                    <?= htmlspecialchars($success) ?>
                    <p><a href="/api/pages/connexion.php">Se connecter</a></p>
                </div>
            <?php else: ?>
                <form action="/api/pages/inscription.php" method="post" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required autocomplete="username">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Mot de passe *</label>
                            <div style="position: relative;">
                                <input type="password" id="password" name="password" required autocomplete="new-password" style="width: 100%; padding-right: 40px;">
                                <button type="button" class="toggle-password" data-target="password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; font-size: 1.2rem;">👁️</button>
                            </div>
                            <small id="pwd-counter" style="color: #888;">0 / 8 — minimum 8 caractères</small>
                            <div id="pwd-strength-bar" style="margin-top: 6px; height: 5px; border-radius: 3px; background: #e0e0e0; overflow: hidden; display: none;">
                                <div id="pwd-strength-fill" style="height: 100%; width: 0%; border-radius: 3px; transition: width 0.3s ease, background-color 0.3s ease;"></div>
                            </div>
                            <small id="pwd-strength-label" style="display: none; font-size: 0.78rem; margin-top: 3px;"></small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe *</label>
                            <div style="position: relative;">
                                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" style="width: 100%; padding-right: 40px;">
                                <button type="button" class="toggle-password" data-target="confirm_password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; font-size: 1.2rem;">👁️</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone">
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse complète</label>
                        <textarea id="adresse" name="adresse" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary">S'inscrire</button>
                </form>
                
                <div class="auth-links" style="margin-top: 20px;">
                    <p>Déjà inscrit ? <a href="/api/pages/connexion.php">Se connecter</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Script pour afficher/masquer les mots de passe
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🙈'; // Oeil fermé
        } else {
            input.type = 'password';
            this.textContent = '👁️'; // Oeil ouvert
        }
    });
});
</script>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>
