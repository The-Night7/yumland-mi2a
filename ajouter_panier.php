<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/panier.php';

// Vérifier si l'utilisateur a soumis le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['cart_message'] = 'Erreur de sécurité, veuillez réessayer.';
        redirect('/public/html/carte.php');
    }
    
    // Récupérer les données du formulaire
    $plat_id = isset($_POST['plat_id']) ? (int)$_POST['plat_id'] : 0;
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
    $options = isset($_POST['options']) ? $_POST['options'] : [];
    
    // Vérifier que la quantité est valide
    if ($quantite <= 0) {
        $quantite = 1;
    }
    
    // Ajouter le plat au panier
    if (addToCart($plat_id, $quantite, $options)) {
        $_SESSION['cart_message'] = 'Le plat a été ajouté à votre panier.';
    } else {
        $_SESSION['cart_message'] = 'Impossible d\'ajouter ce plat au panier.';
    }
    
    // Rediriger vers la page précédente ou la carte
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/public/html/carte.php';
    redirect($referer);
} else {
    // Si l'utilisateur accède directement à cette page sans POST, rediriger vers la carte
    redirect('/public/html/carte.php');
}