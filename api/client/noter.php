<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// L'utilisateur doit être connecté
if (!isLoggedIn()) {
    redirect('/api/pages/connexion.php');
}

$user_id = $_SESSION['user_id'];
$commande_id = isset($_GET['commande_id']) ? (int)$_GET['commande_id'] : 0;

// Si c'est un POST on récupère l'ID depuis le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commande_id'])) {
    $commande_id = (int)$_POST['commande_id'];
}

$message = '';
$messageType = 'success';

// Vérifier que la commande existe, appartient à l'utilisateur et est terminée
$stmt = $pdo->prepare("SELECT * FROM Commandes WHERE id_commande = ? AND id_client = ? AND statut = 'Livrée'");
$stmt->execute([$commande_id, $user_id]);
$commande = $stmt->fetch();

if (!$commande) {
    // Si la commande n'est pas "Livrée" ou n'existe pas, on redirige vers le profil
    header('Location: /api/client/profil.php');
    exit;
}

// Traitement de l'avis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'noter') {
    $note = isset($_POST['note']) ? (int)$_POST['note'] : 5;
    $commentaire = trim($_POST['commentaire'] ?? '');

    try {
        // Enregistrement de l'avis dans la table Avis (si elle existe, sinon on la crée à la volée)
        $tableExists = $pdo->query("SHOW TABLES LIKE 'Avis'")->rowCount() > 0;
        if (!$tableExists) {
            $pdo->exec("CREATE TABLE Avis (
                id_avis INT AUTO_INCREMENT PRIMARY KEY,
                id_commande INT NOT NULL,
                id_client INT NOT NULL,
                note INT NOT NULL,
                commentaire TEXT,
                date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_commande (id_commande)
            )");
        }
        
        // Ajout ou mise à jour de l'avis
        $stmtInsert = $pdo->prepare("INSERT INTO Avis (id_commande, id_client, note, commentaire) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE note = ?, commentaire = ?");
        $stmtInsert->execute([$commande_id, $user_id, $note, $commentaire, $note, $commentaire]);
        
        $message = "⭐ Merci pour votre retour ! Votre avis a été enregistré avec succès.";
    } catch (Exception $e) {
        $message = "Erreur lors de l'enregistrement de votre avis.";
        $messageType = 'danger';
    }
}

$currentPage = 'profil';
$pageTitle = 'Noter la commande #' . $commande_id;
include_once __DIR__ . '/../includes/header.php';
?>

<section class="container form-page">
    <div class="form-container card-style">
        <h2>📝 Évaluer la commande #<?= $commande_id ?></h2>
        <p style="color: #666; margin-bottom: 20px;">Votre avis est précieux pour aider notre Chef à s'améliorer !</p>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
            <a href="/api/client/profil.php" class="btn-primary" style="display: block; text-align: center; background: var(--color-coal-black);">Retour au profil</a>
        <?php else: ?>
            <form action="/api/client/noter.php" method="POST">
                <input type="hidden" name="action" value="noter">
                <input type="hidden" name="commande_id" value="<?= $commande_id ?>">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="note">Note sur 5 étoiles ⭐ :</label>
                    <input type="number" id="note" name="note" min="1" max="5" value="5" required style="width: 100%; padding: 10px; font-size: 1.2rem;">
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="commentaire">Commentaire (optionnel) :</label>
                    <textarea id="commentaire" name="commentaire" rows="4" placeholder="Qu'avez-vous pensé de votre repas ?"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">Envoyer mon avis</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>