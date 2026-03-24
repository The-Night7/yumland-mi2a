<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = intval($_POST['id_produit']);
    
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    // On stocke l'ID et on incrémente la quantité
    if (isset($_SESSION['panier'][$id_produit])) {
        $_SESSION['panier'][$id_produit]++;
    } else {
        $_SESSION['panier'][$id_produit] = 1;
    }

    echo json_encode(["success" => true, "count" => array_sum($_SESSION['panier'])]);
}