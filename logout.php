<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Déconnecter l'utilisateur
logoutUser();

// Rediriger vers la page d'accueil
redirect('/index.php');