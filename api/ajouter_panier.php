<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/plats.php';
// Inclusion des fonctions du panier s'il y en a
require_once __DIR__ . '/includes/panier.php';

// Indique que la réponse sera du JSON (pour le JavaScript)
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = isset($_POST['id_produit']) ? (int)$_POST['id_produit'] : 0;
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
    
    // Récupération des options choisies par le client via le JS du front-end
    $options = isset($_POST['options']) ? json_decode($_POST['options'], true) : [];
    if (!is_array($options)) $options = [];

    if ($id_produit > 0) {
        // 1. Récupérer les informations du plat dans la base
        $plat = getPlatById($id_produit);
        
        if ($plat) {
            // 2. Initialiser la session du panier si elle est vide
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = ['items' => [], 'total' => 0];
            }

            // 3. Chercher si le plat est déjà dans le panier pour juste augmenter la quantité
            $found = false;
            foreach ($_SESSION['cart']['items'] as &$item) {
                // On vérifie que c'est le même ID ET les mêmes options exactes
                $isSameProduct = ((isset($item['id']) && $item['id'] == $id_produit) || (isset($item['plat_id']) && $item['plat_id'] == $id_produit));
                $hasSameOptions = (isset($item['options']) && $item['options'] == $options);

                if ($isSameProduct && $hasSameOptions) {
                     $item['quantite'] += $quantite;
                     $found = true;
                     break;
                }
            }

            // 4. Si c'est un nouveau plat, on l'ajoute au tableau
            if (!$found) {
                $_SESSION['cart']['items'][] = [
                    'id' => $id_produit,
                    'plat_id' => $id_produit,
                    'nom' => $plat['nom'],
                    'prix_unitaire' => $plat['prix'],
                    'quantite' => $quantite,
                    'image' => $plat['image'],
                    'options' => $options
                ];
            }

            // 5. Recalcul de sécurité du Total
            if (function_exists('updateCartTotal')) {
                updateCartTotal();
            } else {
                $total = 0;
                foreach ($_SESSION['cart']['items'] as $item) {
                    $total += $item['prix_unitaire'] * $item['quantite'];
                }
                $_SESSION['cart']['total'] = $total;
            }

            // 6. On renvoie le succès et le nouveau nombre d'articles
            $count = function_exists('getCartItemCount') ? getCartItemCount() : count($_SESSION['cart']['items']);
            echo json_encode(['success' => true, 'count' => $count]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout au panier.']);
?>