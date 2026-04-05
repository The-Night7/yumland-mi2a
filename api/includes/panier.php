<?php
/**
 * Fonctions liées à la gestion du panier
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/plats.php';

/**
 * Initialise le panier s'il n'existe pas déjà
 */
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [],
            'total' => 0
        ];
    }
}

/**
 * Ajoute un plat au panier
 * @param int $plat_id ID du plat
 * @param int $quantite Quantité à ajouter
 * @param array $options Options du plat (optionnel)
 * @return bool Succès ou échec
 */
function addToCart($plat_id, $quantite = 1, $options = []) {
    initCart();
    
    // Récupérer les informations du plat
    $plat = getPlatById($plat_id);
    if (!$plat) {
        return false;
    }
    
    // Vérifier si le plat est déjà dans le panier
    $found = false;
    foreach ($_SESSION['cart']['items'] as &$item) {
        if ($item['plat_id'] == $plat_id && $item['options'] == $options) {
            // Mettre à jour la quantité
            $item['quantite'] += $quantite;
            $found = true;
            break;
        }
    }
    
    // Si le plat n'est pas dans le panier, l'ajouter
    if (!$found) {
        $_SESSION['cart']['items'][] = [
            'plat_id' => $plat_id,
            'nom' => $plat['nom'],
            'prix_unitaire' => $plat['prix'],
            'quantite' => $quantite,
            'options' => $options,
            'image' => $plat['image'],
            'options_dispos' => !empty($plat['options_config']) ? json_encode($plat['options_config']) : '[]'
        ];
    }
    
    // Recalculer le total
    updateCartTotal();
    
    return true;
}

/**
 * Supprime un plat du panier
 * @param int $index Index de l'élément à supprimer
 * @return bool Succès ou échec
 */
function removeFromCart($index) {
    initCart();
    
    if (isset($_SESSION['cart']['items'][$index])) {
        // Supprimer l'élément
        unset($_SESSION['cart']['items'][$index]);
        // Réindexer le tableau
        $_SESSION['cart']['items'] = array_values($_SESSION['cart']['items']);
        // Recalculer le total
        updateCartTotal();
        return true;
    }
    
    return false;
}

/**
 * Met à jour la quantité d'un plat dans le panier
 * @param int $index Index de l'élément à mettre à jour
 * @param int $quantite Nouvelle quantité
 * @param string|null $note Note spéciale ou instruction (optionnel)
 * @return bool Succès ou échec
 */
function updateCartQuantity($index, $quantite, $note = null) {
    initCart();
    
    if (isset($_SESSION['cart']['items'][$index])) {
        if ($quantite <= 0) {
            return removeFromCart($index);
        }
        
        $_SESSION['cart']['items'][$index]['quantite'] = $quantite;
        if ($note !== null) {
            $_SESSION['cart']['items'][$index]['note'] = trim($note);
        }
        updateCartTotal();
        return true;
    }
    
    return false;
}

/**
 * Recalcule le total du panier
 */
function updateCartTotal() {
    initCart();
    
    $total = 0;
    foreach ($_SESSION['cart']['items'] as $item) {
        $total += $item['prix_unitaire'] * $item['quantite'];
    }
    
    $_SESSION['cart']['total'] = $total;
}

/**
 * Récupère le contenu du panier
 * @return array Contenu du panier
 */
function getCart() {
    initCart();
    return $_SESSION['cart'];
}

/**
 * Vide le panier
 */
function clearCart() {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
}

/**
 * Compte le nombre d'articles dans le panier
 * @return int Nombre d'articles
 */
function getCartItemCount() {
    initCart();
    
    $count = 0;
    foreach ($_SESSION['cart']['items'] as $item) {
        $count += $item['quantite'];
    }
    
    return $count;
}