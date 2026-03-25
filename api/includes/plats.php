<?php
// api/includes/plats.php
require_once __DIR__ . '/config.php';

function getPlatById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM Produits WHERE id_produit = ?");
        $stmt->execute([$id]);
        $plat = $stmt->fetch();
        
        if ($plat) {
            return [
                'id' => $plat['id_produit'],
                'nom' => $plat['nom'],
                'prix' => $plat['prix'],
                'image' => $plat['image_url'] ?? '',
                'categorie' => $plat['categorie'] ?? '',
                'description' => $plat['description'] ?? '',
                'options_config' => !empty($plat['options_config']) ? json_decode($plat['options_config'], true) : []
            ];
        }
    } catch (PDOException $e) {
        // Gestion de l'erreur
    }
    return null;
}
?>