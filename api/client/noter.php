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
$stmt = $pdo->prepare("SELECT c.*, 
        (SELECT GROUP_CONCAT(CONCAT(cc.quantite, 'x ', p.nom) SEPARATOR ', ')
         FROM Contenu_Commandes cc 
         JOIN Produits p ON cc.id_produit = p.id_produit 
         WHERE cc.id_commande = c.id_commande) AS plats_commandes
    FROM Commandes c WHERE id_commande = ? AND id_client = ? AND statut = 'Livrée'");
$stmt->execute([$commande_id, $user_id]);
$commande = $stmt->fetch();

if (!$commande) {
    // Si la commande n'est pas "Livrée" ou n'existe pas, on redirige vers le profil
    header('Location: /api/client/profil.php');
    exit;
}

// Traitement de l'avis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'noter') {
    $delivery_note = isset($_POST['delivery_note']) ? (int)$_POST['delivery_note'] : 0;
    $food_note = isset($_POST['food_note']) ? (int)$_POST['food_note'] : 0;
    $commentaire = trim($_POST['commentaire'] ?? '');
    
    // Calcul de la note globale
    $note_globale = round(($delivery_note + $food_note) / 2);

    try {
        // Enregistrement de l'avis dans la table Avis (modifiée pour 2 notes)
        $tableExists = $pdo->query("SHOW TABLES LIKE 'Avis'")->rowCount() > 0;
        if (!$tableExists) {
            $pdo->exec("CREATE TABLE Avis (
                id_avis INT AUTO_INCREMENT PRIMARY KEY,
                id_commande INT NOT NULL,
                id_client INT NOT NULL,
                note_globale INT,
                note_livreur INT,
                note_nourriture INT,
                commentaire TEXT,
                date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_commande (id_commande)
            )");
            $pdo->exec("DROP TABLE IF EXISTS Evaluations");
        } else {
            try {
                $pdo->exec("ALTER TABLE Avis ADD COLUMN note_globale INT AFTER id_client");
            } catch (Exception $e) {
                // La colonne existe déjà
            }
            $pdo->exec("DROP TABLE IF EXISTS Evaluations");
        }
        
        // Ajout ou mise à jour de l'avis
        $stmtInsert = $pdo->prepare("INSERT INTO Avis (id_commande, id_client, note_globale, note_livreur, note_nourriture, commentaire) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE note_globale = ?, note_livreur = ?, note_nourriture = ?, commentaire = ?");
        $stmtInsert->execute([$commande_id, $user_id, $note_globale, $delivery_note, $food_note, $commentaire, $note_globale, $delivery_note, $food_note, $commentaire]);
        
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

<style>
    .stars {
        display: flex;
        gap: 5px;
        font-size: 2rem;
        cursor: pointer;
        color: #ccc; /* Étoiles vides */
        margin-bottom: 20px;
    }
    .star.active {
        color: #f39c12; /* Étoiles pleines (dorées) */
    }
    .form-group label {
        display: block;
        text-align: left;
        font-weight: bold;
        margin-bottom: 8px;
    }
    .readonly-input {
        width: 100%;
        padding: 10px;
        background-color: #f5f5f5;
        border: 1px solid #ccc;
        border-radius: 4px;
        color: #555;
    }
    h3 {
        margin-top: 15px;
        margin-bottom: 10px;
        font-size: 1.1rem;
    }
</style>

<section class="container form-page">
    <div class="form-container card-style">
        <h2>📝 Évaluer la commande #<?= htmlspecialchars($commande_id) ?></h2>
        <p style="color: #666; margin-bottom: 20px;">Comment s'est passé votre expérience "Grand Miam" ?</p>
        
        <div style="background: #fffdf7; padding: 15px; border-radius: 8px; border-left: 4px solid var(--color-primary); margin-bottom: 20px; font-size: 0.9rem;">
            <strong><i class="far fa-calendar-alt"></i> Commande du <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></strong><br>
            <span style="color: #555;"><i class="fas fa-utensils"></i> Plats : <?= htmlspecialchars($commande['plats_commandes']) ?></span>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 20px;">
                <?= htmlspecialchars($message) ?>
            </div>
            <a href="/api/client/profil.php" class="btn-primary" style="display: block; text-align: center; background: var(--color-coal-black);">Retour au profil</a>
        <?php else: ?>
            <form action="/api/client/noter.php" method="POST" id="rating-form">
                <input type="hidden" name="action" value="noter">
                <input type="hidden" name="commande_id" value="<?= htmlspecialchars($commande_id) ?>">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="order-id">Identifiant de la commande :</label>
                    <input type="text" id="order-id" class="readonly-input" value="#CMD-<?= htmlspecialchars($commande_id) ?>" readonly>
                </div>

                <h3>Note du livreur :</h3>
                <div class="stars" id="delivery-star-rating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" id="delivery-rating-value" name="delivery_note" value="0">

                <h3>Note de la nourriture :</h3>
                <div class="stars" id="food-star-rating">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" id="food-rating-value" name="food_note" value="0">

                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="comment">Votre commentaire :</label>
                    <textarea id="comment" name="commentaire" rows="4" style="width: 100%; padding: 10px;" placeholder="Le burger était-il assez chaud ? Le livreur sympa ?"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%;">Envoyer mon avis</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Fonction pour gérer le système d'étoiles
        const setupStarRating = (containerId, hiddenInputId) => {
            const stars = document.querySelectorAll(`#${containerId} .star`);
            const ratingInput = document.getElementById(hiddenInputId);

            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = parseInt(star.getAttribute('data-value'));
                    ratingInput.value = value;

                    // Mettre à jour visuellement les étoiles
                    stars.forEach(s => {
                        const sVal = parseInt(s.getAttribute('data-value'));
                        if(sVal <= value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
        };

        // Initialisation des deux systèmes de notation
        setupStarRating('delivery-star-rating', 'delivery-rating-value');
        setupStarRating('food-star-rating', 'food-rating-value');

        // Validation avant l'envoi du formulaire
        const ratingForm = document.getElementById('rating-form');
        if (ratingForm) {
            ratingForm.addEventListener('submit', (e) => {
                const deliveryNote = document.getElementById('delivery-rating-value').value;
                const foodNote = document.getElementById('food-rating-value').value;

                if (deliveryNote == 0 || foodNote == 0) {
                    e.preventDefault(); // Empêche l'envoi du formulaire
                    alert("Veuillez sélectionner au moins une étoile pour le livreur et la nourriture.");
                }
            });
        }
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>