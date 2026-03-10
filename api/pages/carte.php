<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/plats.php';
require_once __DIR__ . '/../includes/panier.php';

// Récupérer toutes les catégories
$categories = getAllCategories();

// Récupérer tous les plats, éventuellement filtrés par catégorie
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : null;
$plats = getAllPlats($categorie_filter);

// Message pour l'ajout au panier
$message = '';
if (isset($_SESSION['cart_message'])) {
    $message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

// Définir la page courante pour le menu actif
$currentPage = 'carte';
$pageTitle = 'Notre Carte';

// Inclure le header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="menu-header">
    <div class="container">
        <h1>Notre Carte</h1>
        <p>Découvrez nos spécialités de viandes grillées et burgers gourmets</p>
    </div>
</section>

<?php if (!empty($message)): ?>
    <div class="alert alert-success container">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<section class="menu-filters">
    <div class="container">
        <div class="filter-buttons">
            <a href="/api/pages/carte.php" class="filter-btn <?= !$categorie_filter ? 'active' : '' ?>">Tous</a>
            <?php foreach ($categories as $categorie): ?>
                <a href="/public/html/carte.php?categorie=<?= urlencode($categorie) ?>" 
                   class="filter-btn <?= $categorie_filter === $categorie ? 'active' : '' ?>">
                    <?= htmlspecialchars($categorie) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="menu-items">
    <div class="container">
        <?php if (empty($plats)): ?>
            <p class="no-items">Aucun plat disponible dans cette catégorie.</p>
        <?php else: ?>
            <div class="menu-grid">
                <?php foreach ($plats as $plat): ?>
                    <div class="menu-item card-style">
                        <div class="menu-item-image">
                            <img src="/<?= htmlspecialchars($plat['image']) ?>" alt="<?= htmlspecialchars($plat['nom']) ?>">
                        </div>
                        <div class="menu-item-content">
                            <h3><?= htmlspecialchars($plat['nom']) ?></h3>
                            <p class="menu-item-description"><?= htmlspecialchars($plat['description']) ?></p>
                            <div class="menu-item-footer">
                                <span class="menu-item-price"><?= number_format($plat['prix'], 2, ',', ' ') ?> €</span>
                                <form action="/api/ajouter_panier.php" method="post" class="add-to-cart-form">
                                    <input type="hidden" name="plat_id" value="<?= $plat['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <button type="submit" class="btn-add-cart">Ajouter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Inclure le footer
include_once __DIR__ . '/../includes/footer.php';
?>