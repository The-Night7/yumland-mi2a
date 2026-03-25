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
    <style>
        /* --- FORCER LE FOOTER EN BAS (STICKY FOOTER) --- */
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Prend au minimum la hauteur de l'écran */
        }
        main {
            flex: 1 0 auto; /* Permet au contenu principal de s'étirer pour pousser le footer */
        }

        /* --- STYLE DU MENU DÉROULANT --- */
        .nav-links .dropdown {
            position: relative;
            display: inline-block;
        }
        .nav-links .dropdown-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .nav-links .dropdown-toggle:hover {
            background: rgba(0,0,0,0.05);
        }
        .nav-links .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background-color: var(--color-bg, #ffffff);
            min-width: 230px;
            box-shadow: 0px 8px 24px rgba(0,0,0,0.15);
            z-index: 1000;
            border-radius: 8px;
            padding: 10px 0;
            list-style: none;
            border: 1px solid #eee;
            margin-top: 5px;
        }
        .nav-links .dropdown:hover .dropdown-menu {
            display: block; /* Affiche le menu au survol */
        }
        .nav-links .dropdown-menu li {
            width: 100%;
            margin: 0;
        }
        .nav-links .dropdown-menu li a {
            color: var(--color-secondary, #333);
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            transition: all 0.2s ease-in-out;
            font-weight: normal;
        }
        .nav-links .dropdown-menu li a:hover {
            background-color: #f8f9fa;
            color: var(--color-primary, #d32f2f);
            padding-left: 25px; /* Petit effet de décalage au survol */
        }
    </style>
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
                        <li><a href="/api/client/profil.php">👤 Mon Profil</a></li>
                        <li><a href="/api/client/commandes.php">📦 Mes Commandes</a></li>
                        <?php if (hasRole('Administrateur')): ?>
                            <li><a href="/api/admin/dashboard.php">🛡️ Administration</a></li>
                        <?php elseif (hasRole('Restaurateur')): ?>
                            <li><a href="/api/restaurateur/commandes.php">👨‍🍳 Écran Cuisine</a></li>
                        <?php elseif (hasRole('Livreur')): ?>
                            <li><a href="/api/livreur/livraisons.php">🛵 Mes Livraisons</a></li>
                        <?php endif; ?>
                        <li><a href="/api/logout.php">🚪 Déconnexion</a></li>
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