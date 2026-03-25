<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/plats.php';
require_once __DIR__ . '/includes/panier.php';

// Configuration de la réponse pour l'appel AJAX
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = isset($_POST['id_produit']) ? (int)$_POST['id_produit'] : 0;
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
    
    // Parsing des options personnalisées (ex: cuisson, type de boisson)
    $options = [];
    if (isset($_POST['options'])) {
        if (is_array($_POST['options'])) {
            $options = $_POST['options'];
        } else {
            $options = json_decode($_POST['options'], true) ?: [];
        }
    }

    if ($id_produit > 0) {
        $plat = getPlatById($id_produit);
        
        if ($plat) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = ['items' => [], 'total' => 0];
            }

            // Vérifie si le plat existe déjà avec la même configuration exacte
            $found = false;
            foreach ($_SESSION['cart']['items'] as &$item) {
                $isSameProduct = ((isset($item['id']) && $item['id'] == $id_produit) || (isset($item['plat_id']) && $item['plat_id'] == $id_produit));
                $hasSameOptions = (isset($item['options']) && $item['options'] == $options);

                if ($isSameProduct && $hasSameOptions) {
                     $item['quantite'] += $quantite;
                     $found = true;
                     break;
                }
            }

            // Ajout du nouvel article
            if (!$found) {
                // Extrait le prix dynamiquement si une option payante est sélectionnée
                $prix_final = $plat['prix'];
                if (!empty($options) && is_array($options)) {
                    foreach ($options as $opt) {
                        if (preg_match('/-\s*([0-9]+[.,][0-9]{2})\s*€/', $opt, $matches)) {
                            $prix_final = (float)str_replace(',', '.', $matches[1]);
                        }
                    }
                }

                $_SESSION['cart']['items'][] = [
                    'id' => $id_produit,
                    'plat_id' => $id_produit,
                    'nom' => $plat['nom'],
                    'prix_unitaire' => $prix_final,
                    'quantite' => $quantite,
                    'image' => $plat['image'],
                    'options' => $options
                ];
            }

            // Mise à jour du total global
            if (function_exists('updateCartTotal')) {
                updateCartTotal();
            } else {
                $total = 0;
                foreach ($_SESSION['cart']['items'] as $item) {
                    $total += $item['prix_unitaire'] * $item['quantite'];
                }
                $_SESSION['cart']['total'] = $total;
            }

            $count = function_exists('getCartItemCount') ? getCartItemCount() : count($_SESSION['cart']['items']);
            echo json_encode(['success' => true, 'count' => $count]);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout au panier.']);
?>