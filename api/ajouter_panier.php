<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/panier.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = intval($_POST['id_produit']);
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;
    
    $success = addToCart($id_produit, $quantite);
    
    echo json_encode(["success" => $success, "count" => getCartItemCount()]);
}