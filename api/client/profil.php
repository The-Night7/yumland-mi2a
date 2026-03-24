<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// L'utilisateur doit être connecté pour accéder à cette page
if (!isLoggedIn()) {
    redirect('/api/pages/connexion.php');
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = 'success'; // ou 'danger'

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE Utilisateurs SET nom = ?, prenom = ?, tel = ?, adresse = ? WHERE id_user = ?");
        $stmt->execute([$nom, $prenom, $tel, $adresse, $user_id]);
        
        // Mettre à jour le nom en session au cas où il a changé
        $_SESSION['user_name'] = $nom;
        
        $message = "Vos informations ont été mises à jour avec succès !";
    } catch (PDOException $e) {
        $message = "Erreur lors de la mise à jour de vos informations.";
        $messageType = 'danger';
    }
}

// Récupération des informations actuelles de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE id_user = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Détermination du statut Miams
$miams = $user['solde_miams'] ?? 0;
if ($miams < 150) $statut_miams = "Débutant 🥉";
elseif ($miams < 500) $statut_miams = "Argent 🥈";
else $statut_miams = "Or 🥇";

$currentPage = 'profil';
$pageTitle = 'Mon Profil';
include_once __DIR__ . '/../includes/header.php';
?>

<section class="container form-page">
    <div class="form-container card-style">
        <h2>⚙️ Paramètres du compte</h2>
        <div style="background: var(--color-sauce-cream); padding: 15px; border-left: 4px solid var(--color-fry-gold); margin-bottom: 20px;">
            <h3 style="margin-bottom: 5px;">Club Le Grand Miam</h3>
            <p>Solde Miams actuel : <strong><?= htmlspecialchars($miams) ?> 🍔</strong></p>
            <p>Statut fidélité : <strong><?= $statut_miams ?></strong></p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="/api/client/profil.php" method="POST">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="tel">Téléphone :</label>
                <input type="text" id="tel" name="tel" value="<?= htmlspecialchars($user['tel'] ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom: 25px;">
                <label for="adresse">Adresse de livraison par défaut :</label>
                <textarea id="adresse" name="adresse" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%;">Enregistrer les modifications</button>
        </form>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>