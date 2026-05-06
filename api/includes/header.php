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
        header('Location: /api/pages/connexion.php?error=compte_bloque');
        exit;
    }
}

// Récupérer le nombre d'articles dans le panier
$cartItemCount = getCartItemCount();

// Lecture des cookies d'accessibilité côté serveur (évite l'effet de flash blanc au chargement)
$themeClass = (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'dark-mode' : '';
$fontClass = (isset($_COOKIE['font']) && $_COOKIE['font'] === 'dyslexic') ? 'dyslexic-mode' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME ?></title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/css/dark-mode.css?v=<?= time() ?>">
    <!-- Intégration de FontAwesome pour des icônes professionnelles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Intégration de la police OpenDyslexic pour l'accessibilité -->
    <link href="https://fonts.cdnfonts.com/css/opendyslexic" rel="stylesheet">
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <style>
        /* Permet au footer de toujours rester collé en bas de l'écran */
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1 0 auto;
            position: relative;
            z-index: 1 !important; /* Force le contenu à rester tout au fond */
        }
        
        /* Forçage ultra-prioritaire UNIQUEMENT pour le header principal */
        header.main-site-header {
            position: sticky !important;
            top: 0 !important;
            z-index: 999999 !important;
            background-color: rgba(255, 255, 255, 0.98) !important; /* Fond opaque */
            backdrop-filter: none !important; /* FIX NAVIGATEUR : Le flou casse l'empilement */
            -webkit-backdrop-filter: none !important;
        }

        /* Design du menu déroulant (Avatar client) */
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
            z-index: 999999 !important;
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
        
        /* --- MODE DYSLEXIE (Accessibilité) --- */
        .dyslexic-mode *:not(i):not(.fas):not(.fa):not(.far) {
            font-family: 'OpenDyslexic', 'Comic Sans MS', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji', sans-serif !important;
            letter-spacing: 0.05em !important;
            word-spacing: 0.1em !important;
            line-height: 1.6 !important;
        }
    </style>
    <script defer src="/js/script.js"></script>
    <script defer>
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
            
            // Gestion des boutons d'accessibilité
            const btnDark = document.getElementById('toggle-dark-mode');
            const btnDyslexic = document.getElementById('toggle-dyslexic-mode');

            if (btnDark) {
                btnDark.addEventListener('click', function() {
                    document.body.classList.toggle('dark-mode');
                    document.cookie = "theme=" + (document.body.classList.contains('dark-mode') ? "dark" : "light") + "; path=/; max-age=31536000";
                });
            }

            if (btnDyslexic) {
                btnDyslexic.addEventListener('click', function() {
                    document.body.classList.toggle('dyslexic-mode');
                    document.cookie = "font=" + (document.body.classList.contains('dyslexic-mode') ? "dyslexic" : "standard") + "; path=/; max-age=31536000";
                });
            }
        });
    </script>
    <script defer src="/js/form-validation.js"></script>
</head>
<body class="<?= trim($themeClass . ' ' . $fontClass) ?>">

<header class="main-site-header">
    <nav>
        <div class="logo-container">
            <a href="/api/index.php" class="logo-text">Le <span class="text-highlight">Grand</span> Miam</a>
        </div>
        <ul class="nav-links">
            <li><a href="/api/index.php" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Accueil</a></li>
            <li><a href="/api/pages/carte.php" class="<?= $currentPage === 'carte' ? 'active' : '' ?>">La Carte</a></li>
            <li><a href="/api/pages/avis.php" class="<?= $currentPage === 'avis' ? 'active' : '' ?>">Avis</a></li>
            
            <?php if (isLoggedIn()): ?>
                <!-- Boutons d'accès rapide pour le Staff -->
                <?php if (hasRole('Restaurateur')): ?>
                    <li><a href="/api/restaurateur/commandes.php" style="font-size: 1.1rem; padding: 12px 18px; background: #e65100; color: white; border-radius: 8px; font-weight: bold;"><i class="fas fa-fire-burner"></i> Cuisine</a></li>
                <?php elseif (hasRole('Livreur')): ?>
                    <li><a href="/api/livreur/livraisons.php" style="font-size: 1.1rem; padding: 12px 18px; background: #2e7d32; color: white; border-radius: 8px; font-weight: bold;"><i class="fas fa-motorcycle"></i> Courses</a></li>
                <?php endif; ?>
                
                <!-- Menu déroulant classique pour TOUS -->
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
            <?php else: ?>
                <li><a href="/api/pages/connexion.php" class="btn-login">Mon Compte</a></li>
            <?php endif; ?>
            
            <!-- Icône du panier avec compteur -->
            <li>
                <a href="/api/panier.php" class="cart-icon">
                    <i class="fas fa-shopping-cart" style="font-size: 1.2rem;"></i>
                    <?php if ($cartItemCount > 0): ?>
                        <span class="cart-count"><?= $cartItemCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li style="display:flex; flex-direction:column; gap:5px; align-items:flex-end;">
                <button id="toggle-dark-mode" style="background:none; border:1px solid var(--color-grey-light); border-radius:20px; padding:4px 10px; cursor:pointer; font-size:0.8rem; color: inherit;">
                    🌓 Mode Sombre
                </button>
                <button id="toggle-dyslexic-mode" style="background:none; border:1px solid var(--color-grey-light); border-radius:20px; padding:4px 10px; cursor:pointer; font-size:0.8rem; color: inherit;">
                    👁️ Dyslexie
                </button>
            </li>
        </ul>
    </nav>
</header>

<main>