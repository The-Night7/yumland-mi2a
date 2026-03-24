<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Déconnecter l'utilisateur
logoutUser();

// Rediriger vers la page d'accueil avec le bon chemin
redirect('/api/index.php');