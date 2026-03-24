<?php
// api/includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/panier.php';

// Récupérer le nombre d'articles dans le panier
$cartItemCount = getCartItemCount();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>

<header>
    <nav>
        <div class="logo-container">
            <a href="/api/index.php" class="logo-text">Le <span class="text-highlight">Grand</span> Miam</a>
        </div>
        <ul class="nav-links">
            <li><a href="/api/index.php" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Accueil</a></li>
            <li><a href="/api/pages/carte.php" class="<?= $currentPage === 'carte' ? 'active' : '' ?>">La Carte</a></li>
            
            <?php if (isLoggedIn()): ?>
                <!-- Menu déroulant pour utilisateur connecté -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <?= htmlspecialchars($_SESSION['user_name']) ?> 
                        <span class="dropdown-icon">▼</span>
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (hasRole('Administrateur')): ?>
                            <li><a href="/api/admin/dashboard.php">Administration</a></li>
                        <?php elseif (hasRole('Restaurateur')): ?>
                            <li><a href="/api/restaurateur/commandes.php">Commandes</a></li>
                        <?php elseif (hasRole('Livreur')): ?>
                            <li><a href="/api/livreur/livraisons.php">Mes Livraisons</a></li>
                        <?php else: ?>
                            <li><a href="/api/client/profil.php">Mon Profil</a></li>
                            <li><a href="/api/client/commandes.php">Mes Commandes</a></li>
                        <?php endif; ?>
                        <li><a href="/api/logout.php">Déconnexion</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li><a href="/api/pages/connexion.php" class="btn-login">Mon Compte</a></li>
            <?php endif; ?>
            
            <!-- Icône du panier avec compteur -->
            <li>
                <a href="/api/panier.php" class="cart-icon">
                    🛒
                    <?php if ($cartItemCount > 0): ?>
                        <span class="cart-count"><?= $cartItemCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </nav>
</header>

<main>