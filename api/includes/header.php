<?php
// api/includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/panier.php';

// Vérifier si l'utilisateur connecté est bloqué
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT statut FROM Utilisateurs WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if ($u && $u['statut'] === 'Bloqué') {
        session_destroy();
        header('Location: /api/connexion.php?msg=compte_bloque');
        exit;
    }
}

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
    <!-- Intégration de FontAwesome pour des icônes professionnelles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            margin-top: 0; /* Suppression de la marge pour éviter que la souris sorte de la zone et ferme le menu */
        }
        .nav-links .dropdown:hover .dropdown-menu {
            display: block; /* Affiche le menu au survol */
        }
        .nav-links .dropdown-menu.show {
            display: block !important; /* Force l'affichage au clic (notamment sur mobile) */
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
    <script defer src="/js/script.js"></script>
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
                <?php if (hasRole('Restaurateur') || hasRole('Livreur')): ?>
                    <!-- Menu éclaté en gros boutons pour le personnel (Tablette / Gants) -->
                    <li><a href="/api/client/profil.php" style="font-size: 1.1rem; padding: 12px 18px; border: 2px solid var(--color-grey-light); border-radius: 8px;"><i class="fas fa-user-circle"></i> Profil</a></li>
                    <?php if (hasRole('Restaurateur')): ?>
                        <li><a href="/api/restaurateur/commandes.php" style="font-size: 1.1rem; padding: 12px 18px; background: #e65100; color: white; border-radius: 8px; font-weight: bold;"><i class="fas fa-fire-burner"></i> Cuisine</a></li>
                    <?php elseif (hasRole('Livreur')): ?>
                        <li><a href="/api/livreur/livraisons.php" style="font-size: 1.1rem; padding: 12px 18px; background: #2e7d32; color: white; border-radius: 8px; font-weight: bold;"><i class="fas fa-motorcycle"></i> Courses</a></li>
                    <?php endif; ?>
                    <li><a href="/api/logout.php" style="font-size: 1.1rem; padding: 12px 18px; background: var(--color-primary); color: white; border-radius: 8px; font-weight: bold;"><i class="fas fa-sign-out-alt"></i> Quitter</a></li>
                <?php else: ?>
                    <!-- Menu déroulant classique pour Client et Administrateur -->
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle">
                            <?= htmlspecialchars($_SESSION['user_name']) ?> 
                            <span class="dropdown-icon">▼</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="/api/client/profil.php"><i class="fas fa-user"></i> Mon Profil</a></li>
                            <li><a href="/api/client/commandes.php"><i class="fas fa-box-open"></i> Mes Commandes</a></li>
                            <?php if (hasRole('Administrateur')): ?>
                                <li><a href="/api/admin/dashboard.php"><i class="fas fa-shield-alt"></i> Administration</a></li>
                            <?php endif; ?>
                            <li><a href="/api/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="/api/pages/connexion.php" class="btn-login">Mon Compte</a></li>
            <?php endif; ?>
            
            <!-- Icône du panier avec compteur (Caché pour le personnel en service) -->
            <?php if (!isLoggedIn() || (!hasRole('Restaurateur') && !hasRole('Livreur'))): ?>
            <li>
                <a href="/api/panier.php" class="cart-icon">
                    <i class="fas fa-shopping-cart" style="font-size: 1.2rem;"></i>
                    <?php if ($cartItemCount > 0): ?>
                        <span class="cart-count"><?= $cartItemCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<script>
// Script pour rendre le menu déroulant persistant au clic (très utile sur mobile et tablette)
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    
    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        });
        
        // Ferme le menu si on clique en dehors
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
</script>

<main>