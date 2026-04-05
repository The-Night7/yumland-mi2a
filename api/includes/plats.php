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
            $options_config = !empty($plat['options_config']) ? $plat['options_config'] : '[]';
            
            // Fallback d'auto-réparation pour les menus si la base de données n'est pas à jour
            if ($options_config === '[]') {
                $fallbacks = [
                    'Formule LUNCH EXPRESS' => '[{"titre":"Plat","choix":["Burger Le Grand Miam","Le Pavé du Chef","Veggie Grill"]},{"titre":"Cuisson (si viande)","choix":["Saignant","À point","Bien cuit","Sans objet (Veggie)"]},{"titre":"Préparation","choix":["Standard","Viande Halal","Sans objet"]},{"titre":"Boisson","choix":["Coca-Cola (33cl)","Coca Zéro (33cl)","Fanta (33cl)","Sprite (33cl)","Ice Tea (33cl)","Verre de vin (12cl)","Café"]}]',
                    'Menu LITTLE COWBOY' => '[{"titre":"Plat","choix":["Mini Cheeseburger","Nuggets de Poulet"]},{"titre":"Viande","choix":["Standard","Halal"]},{"titre":"Accompagnement","choix":["Frites","Haricots verts"]},{"titre":"Dessert","choix":["Sundae Vanille Chocolat","Sundae Vanille Fraise","Compote de fruits"]},{"titre":"Boisson","choix":["Sirop à l\'eau","Jus de pomme"]}]',
                    'Menu GRILL MASTER' => '[{"titre":"Entrée","choix":["Onion Rings","Os à Moelle","Œuf Mayo"]},{"titre":"Plat","choix":["Burger Le Grand Miam","Burger Cheesy Tower","Burger Montagnard","Burger Frenchy","BBQ Ribs","Magret de Canard","Le Pavé du Chef","Pavé de Saumon"]},{"titre":"Cuisson (si viande)","choix":["Bleu","Saignant","À point","Bien cuit","Sans objet"]},{"titre":"Préparation","choix":["Standard","Viande Halal","Sans objet"]},{"titre":"Dessert","choix":["Cheesecake","Brioche Perdue","Coupe de Glace 3 boules"]},{"titre":"Boisson","choix":["Pinte de Bière","Coca-Cola (50cl)","Coca Zéro (50cl)","Fanta (50cl)","Sprite (50cl)","Ice Tea (50cl)"]}]',
                    'Sauce Supplémentaire' => '[{"titre":"Sauce","choix":["Sauce BBQ","Sauce Béarnaise","Sauce au Poivre","Sauce Roquefort","Moutarde Ancienne"]}]',
                    'L\'Entrecôte XXL' => '[{"titre":"Cuisson","choix":["Bleu","Saignant","À point","Bien cuit"]},{"titre":"Préparation","choix":["Standard","Viande Halal"]}]',
                    'Le Pavé du Chef' => '[{"titre":"Cuisson","choix":["Bleu","Saignant","À point","Bien cuit"]},{"titre":"Préparation","choix":["Standard","Viande Halal"]}]',
                    'La Côte de Bœuf' => '[{"titre":"Cuisson","choix":["Bleu","Saignant","À point","Bien cuit"]},{"titre":"Préparation","choix":["Standard","Viande Halal"]}]',
                    'Magret de Canard' => '[{"titre":"Cuisson","choix":["Rosé","À point","Bien cuit"]}]',
                    'Le Grand Miam' => '[{"titre":"Cuisson","choix":["Saignant","À point","Bien cuit"]},{"titre":"Préparation","choix":["Standard","Viande Halal"]}]',
                    'Le Cheesy Tower' => '[{"titre":"Cuisson","choix":["Saignant","À point","Bien cuit"]},{"titre":"Préparation","choix":["Standard","Viande Halal"]}]',
                    'Le Montagnard' => '[{"titre":"Cuisson","choix":["Saignant","À point","Bien cuit"]},{"titre":"Préparation","choix":["Standard (avec Lardons)","Sans Lardons","Viande Halal (sans Lardons)"]}]',
                    'Le Frenchy' => '[{"titre":"Cuisson","choix":["Saignant","À point","Bien cuit"]},{"titre":"Préparation","choix":["Standard","Viande Halal"]}]',
                    'Veggie Grill' => '[{"titre":"Galette","choix":["Haricots Rouges/Maïs","Simili-carné"]}]',
                    'Sodas' => '[{"titre":"Choix","choix":["Coca-Cola","Coca-Cola Zéro","Fanta","Sprite"]}]',
                    'Ice Tea' => '[{"titre":"Choix","choix":["Fuze Tea","Lipton"]}]',
                    'Limonade Artisanale' => '[{"titre":"Parfum","choix":["Citron","Violette"]}]',
                    'Jus de Fruits' => '[{"titre":"Parfum","choix":["Orange","Pomme","Ananas"]}]',
                    'Eaux' => '[{"titre":"Format","choix":["50cl - 3.50 €","1L - 5.50 €"]}]',
                    'Sirop à l\'eau' => '[{"titre":"Parfum","choix":["Grenadine","Menthe","Fraise"]}]',
                    'Bière Pression Blonde' => '[{"titre":"Format","choix":["25cl - 4.00 €","50cl (Pinte) - 7.50 €"]}]',
                    'Bière Pression IPA' => '[{"titre":"Format","choix":["25cl - 5.00 €","50cl (Pinte) - 8.50 €"]}]',
                    'Vin Rouge' => '[{"titre":"Format","choix":["Verre 12cl - 5.00 €","Bouteille - 24.00 €"]}]',
                    'Vin Rosé' => '[{"titre":"Format","choix":["Verre 12cl - 5.00 €","Bouteille - 22.00 €"]}]',
                    'Milkshake Classique US' => '[{"titre":"Parfum","choix":["Vanille","Chocolat","Fraise"]}]'
                ];
                
                if (isset($fallbacks[$plat['nom']])) {
                    $options_config = $fallbacks[$plat['nom']];
                    // Réparation silencieuse dans la base de données
                    $stmtUpdate = $pdo->prepare("UPDATE Produits SET options_config = ? WHERE id_produit = ?");
                    $stmtUpdate->execute([$options_config, $id]);
                }
            }
            
            return [
                'id' => $plat['id_produit'],
                'nom' => $plat['nom'],
                'prix' => $plat['prix'],
                'image' => $plat['image_url'] ?? '',
                'categorie' => $plat['categorie'] ?? '',
                'description' => $plat['description'] ?? '',
                'options_config' => json_decode($options_config, true)
            ];
        }
    } catch (PDOException $e) {
        // Gestion de l'erreur
    }
    return null;
}
?>