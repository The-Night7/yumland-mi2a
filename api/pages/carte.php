<?php 
// Ajoutez cette ligne au tout début, juste avant require_once 'header.php'
$currentPage = 'carte';   
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/plats.php';
require_once __DIR__ . '/../includes/panier.php';

// Récupérer tous les plats, éventuellement filtrés par catégorie
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : null;

// Message pour l'ajout au panier
$message = '';
if (isset($_SESSION['cart_message'])) {
    $message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

// Définir la page courante pour le menu actif
$pageTitle = 'Notre Carte';

// Récupérer les produits pour associer les boutons d'ajout au bon ID
$stmt = $pdo->query("SELECT id_produit, nom FROM Produits");
$tous_les_produits = $stmt->fetchAll();

function getPlatId($recherche, $produits) {
    foreach ($produits as $p) {
        if (stripos($p['nom'], $recherche) !== false) {
            return $p['id_produit'];
        }
    }
    return 1; // ID par défaut si introuvable
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Carte | Le Grand Miam</title>
</head>
<body>

<main class="menu-container">
    <h1>Notre Carte</h1>
    <p class="intro-text">Steakhouse, Grillades & Burgers XXL - Une expérience culinaire unique</p>
    
    <!-- Barre de recherche et filtre -->
    <div class="search-filter" style="margin-bottom:1.5rem; display: flex; gap: 0.5rem;">
        <input type="text" id="searchInput" placeholder="Rechercher un plat…" onkeyup="applyFilters()"
               style="padding:0.5rem; border:1px solid var(--color-grey-light); border-radius:4px; flex-grow: 1;">
        <select id="categoryFilter" onchange="applyFilters()"
                style="padding:0.5rem; border:1px solid var(--color-grey-light); border-radius:4px;">
            <option value="all">Toutes catégories</option>
            <option value="entrees">Entrées</option>
            <option value="viandes">Viandes & Poissons</option>
            <option value="burgers">Burgers</option>
            <option value="desserts">Desserts</option>
            <option value="boissons">Boissons</option>
        </select>
        <select id="specFilter" onchange="applyFilters()"
                style="padding:0.5rem; border:1px solid var(--color-grey-light); border-radius:4px;">
            <option value="all">Toutes spécificités</option>
            <option value="halal">Halal</option>
            <option value="porc">Contient du porc</option>
            <option value="vege">Végétarien</option>
            <option value="poisson">Poisson</option>
        </select>
    </div>
    
    <!-- ENTRÉES -->
    <table class="menu-table" data-category="entrees">
        <caption>🥗 Les Entrées & Partage</caption>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Prix</th>
            <th>Spécificités</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Onion Rings "Tower"</td>
            <td>8 à 10 beignets d'oignons servis sur pique verticale. Sauce BBQ.</td>
            <td>6.50 €</td>
            <td data-spec="vege">🌱 Végétarien</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Onion Rings', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Os à Moelle Rôti</td>
            <td>Coupe longitudinale, fleur de sel, pain de campagne grillé.</td>
            <td>9.00 €</td>
            <td data-spec="halal">☪️ Halal possible</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Moelle', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Planche Mixte</td>
            <td>Charcuterie (Rosette, Terrine) + Fromages (Cantal, Chèvre).</td>
            <td>14.00 €</td>
            <td data-spec="porc">🐷 Contient Porc</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Planche Mixte', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        </tbody>
    </table>
    
    <!-- VIANDES -->
    <table class="menu-table" data-category="viandes">
        <caption>🍖 Le Grill (Viandes & Poissons)</caption>
        <thead>
        <tr>
            <th>Nom de la Pièce</th>
            <th>Détails / Portion</th>
            <th>Prix</th>
            <th>Spécificités</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>L'Entrecôte XXL</td>
            <td>350g - Charolais/Limousin. Persillée.</td>
            <td>24.90 €</td>
            <td data-spec="halal">☪️ Option Halal</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Entrecôte', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Le Pavé du Chef</td>
            <td>200g - Cœur de Rumsteak. Tendre et maigre.</td>
            <td>18.50 €</td>
            <td data-spec="halal">☪️ Option Halal</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Pavé', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>La Côte de Bœuf</td>
            <td>1 kg (pour 2 pers). Maturation min. 21 jours.</td>
            <td>59.00 €</td>
            <td data-spec="halal">☪️ Option Halal</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Côte de Bœuf', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>BBQ Ribs</td>
            <td>Travers de porc marinés 24h, cuisson lente 12h.</td>
            <td>19.50 €</td>
            <td data-spec="porc">🐷 100% Porc</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Ribs', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Magret de Canard</td>
            <td>Entier (300g approx), grillé rosé, sauce miel.</td>
            <td>22.00 €</td>
            <td>Volaille Française</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Magret', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Pavé de Saumon</td>
            <td>180g, grillé unilatéral, citron vert.</td>
            <td>17.50 €</td>
            <td data-spec="poisson">Option Poisson</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Saumon', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>L'Andouillette</td>
            <td>AAAAA, grillée forte à la moutarde.</td>
            <td>16.00 €</td>
            <td data-spec="porc">🐷 100% Porc</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Andouillette', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4">Toutes nos viandes sont servies avec frites à volonté et sauce au choix.</td>
            <td>Supplément sauce + 1.50 €</td>
        </tr>
        </tfoot>
    </table>
    
    <!-- BURGERS -->
    <table class="menu-table" data-category="burgers">
        <caption>🍔 Les Burgers</caption>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Composition</th>
            <th>Prix</th>
            <th>Spécificités</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Le Grand Miam</td>
            <td>Double Steak 150g, Cheddar, Sauce Maison.</td>
            <td>16.90 €</td>
            <td data-spec="halal">☪️ Option Halal</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Grand Miam', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Le Cheesy Tower</td>
            <td>Steak 180g, Sauce Fromagère, Cheddar fondu.</td>
            <td>15.50 €</td>
            <td data-spec="halal">☪️ Option Halal</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Cheesy Tower', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Le Montagnard</td>
            <td>Steak 150g, Reblochon, Lardons, Galette PdeT.</td>
            <td>17.90 €</td>
            <td data-spec="porc">🐷 (Lardons)</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Montagnard', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Le Frenchy</td>
            <td>Steak 150g, Cantal jeune, Oignons confits.</td>
            <td>16.50 €</td>
            <td data-spec="halal">☪️ Option Halal</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Frenchy', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Veggie Grill</td>
            <td>Galette Haricots Rouges/Maïs ou Simili-carné.</td>
            <td>14.50 €</td>
            <td data-spec="vege">🌱 Végétarien</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Veggie', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        </tbody>
    </table>
    
    <!-- DESSERTS -->
    <table class="menu-table" data-category="desserts">
        <caption>🍰 Desserts (Sweet Ending)</caption>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Détails</th>
            <th>Prix</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Profiteroles XXL</td>
            <td>1 Chou géant, Glace Vanille, Chocolat chaud versé à table.</td>
            <td>9.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Profiteroles', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Cookie Skillet</td>
            <td>Cuit minute dans un poêlon, mi-cuit à cœur.</td>
            <td>8.00 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Cookie', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Cheesecake NY</td>
            <td>Base Speculoos, coulis fruits rouges.</td>
            <td>7.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Cheesecake', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Brioche Perdue</td>
            <td>Tranche épaisse, caramel beurre salé.</td>
            <td>6.90 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Brioche', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Café Gourmand</td>
            <td>Café + Mini Cookie + Mini Mousse + Mini Brioche.</td>
            <td>8.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Café Gourmand', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        </tbody>
    </table>
    
    <!-- BOISSONS -->
    <table class="menu-table" data-category="boissons">
        <caption>🍺 Les Boissons</caption>
        <thead>
        <tr>
            <th>Catégorie</th>
            <th>Produit</th>
            <th>Détails / Volume</th>
            <th>Prix</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <!-- Softs & Jus -->
        <tr>
            <td rowspan="6" class="category-cell">Softs & Jus</td>
            <td>Sodas</td>
            <td>Coca-Cola / Zéro, Fanta, Sprite (33cl)</td>
            <td>3.90 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Sodas', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Ice Tea</td>
            <td>Fuze Tea / Lipton (25cl)</td>
            <td>3.90 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Ice Tea', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Limonade Artisanale</td>
            <td>"La French" (Citron ou Violette) - 33cl</td>
            <td>4.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Limonade', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Jus de Fruits</td>
            <td>Orange, Pomme, Ananas (25cl)</td>
            <td>4.00 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Jus', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Eaux</td>
            <td>Vittel, San Pellegrino (50cl / 1L)</td>
            <td>3.50 € / 5.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Eaux', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Sirop à l'eau</td>
            <td>Grenadine, Menthe, Fraise (25cl)</td>
            <td>2.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Sirop', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        
        <tr class="separator"><td colspan="5"></td></tr>
        
        <!-- Bières & Vins -->
        <tr>
            <td rowspan="6" class="category-cell">Bières & Vins</td>
            <td>Bière Pression Blonde</td>
            <td>Premium (25cl / 50cl)</td>
            <td>4.00 € / 7.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Blonde', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Bière Pression IPA</td>
            <td>Craft (25cl / 50cl)</td>
            <td>5.00 € / 8.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('IPA', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Budweiser</td>
            <td>Bouteille 33cl</td>
            <td>5.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Budweiser', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Desperados</td>
            <td>Bouteille 33cl</td>
            <td>6.00 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Desperados', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Vin Rouge</td>
            <td>Côtes du Rhône ou Bordeaux (Verre 12cl / Bouteille)</td>
            <td>5.00 € / 24.00 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Vin Rouge', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Vin Rosé</td>
            <td>Côte de Provence (Verre 12cl / Bouteille)</td>
            <td>5.00 € / 22.00 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Vin Rosé', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        
        <tr class="separator"><td colspan="5"></td></tr>
        
        <!-- Smoothies & Milkshakes -->
        <tr>
            <td rowspan="5" class="category-cell">Smoothies & Milkshakes</td>
            <td>Smoothie "Le Tropical"</td>
            <td>Mangue, Ananas, Passion (Mixé minute)</td>
            <td>6.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Tropical', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Smoothie "Red Kiss"</td>
            <td>Fraise, Framboise, Banane</td>
            <td>6.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Red Kiss', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Milkshake Classique US</td>
            <td>Vanille, Chocolat ou Fraise</td>
            <td>5.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Milkshake Classique', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>FREAKSHAKE "Le Choco-Bomb"</td>
            <td>Milkshake Nutella + Chantilly + Brownie entier + Coulis</td>
            <td>9.90 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Choco-Bomb', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>FREAKSHAKE "Cookie Monster"</td>
            <td>Milkshake Vanille + Chantilly + Éclats de Cookie + Caramel</td>
            <td>9.90 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Cookie Monster', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        
        <tr class="separator"><td colspan="5"></td></tr>
        
        <!-- Cocktails & Mocktails -->
        <tr>
            <td rowspan="4" class="category-cell">Cocktails & Mocktails</td>
            <td>Mojito</td>
            <td>Rhum, Menthe fraîche, Citron vert, Perrier</td>
            <td>8.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Mojito', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Spritz</td>
            <td>Aperol, Prosecco, Rondelle d'orange</td>
            <td>8.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Spritz', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Virgin Mojito</td>
            <td>Version sans alcool</td>
            <td>6.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Virgin', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        <tr>
            <td>Rio</td>
            <td>Jus d'orange, Jus d'ananas, Sirop de grenadine (Bicolore)</td>
            <td>6.50 €</td>
            <td><button class="btn-primary" style="padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; border: none; cursor: pointer;" onclick="ajouterAuPanier(<?= getPlatId('Rio', $tous_les_produits) ?>)">Ajouter 🛒</button></td>
        </tr>
        </tbody>
    </table>
    
    <!-- MENUS & FORMULES -->
    <h2>Nos Menus & Formules</h2>
    
    <div class="menu-formule">
        <h3>💼 Formule "LUNCH EXPRESS" - 16.90 €</h3>
        <p class="formule-details"><em>Disponible uniquement le midi, du lundi au vendredi</em></p>
        <ul>
            <li><strong>PLAT</strong> (au choix) :
                <ul>
                    <li>Burger "Le Grand Miam" (Classique)</li>
                    <li>OU Le Pavé du Chef (Rumsteak 200g)</li>
                    <li>OU Veggie Grill</li>
                </ul>
                <p>Tous servis avec Frites Maison à volonté & Salade</p>
            </li>
            <li><strong>BOISSON</strong> : Soft (33cl), Verre de vin (12cl) ou Café</li>
        </ul>
    </div>
    
    <div class="menu-formule">
        <h3>🤠 Menu "LITTLE COWBOY" - 10.90 €</h3>
        <p class="formule-details"><em>Pour les enfants de moins de 12 ans</em></p>
        <ul>
            <li><strong>PLAT</strong> (au choix) :
                <ul>
                    <li>Mini Cheeseburger (Steak 100g bien cuit)</li>
                    <li>OU Nuggets de Poulet (x6)</li>
                </ul>
                <p>Accompagnement : Frites ou Haricots verts</p>
            </li>
            <li><strong>DESSERT</strong> :
                <ul>
                    <li>Sundae Vanille (Nappage chocolat ou Fraise)</li>
                    <li>OU Compote de fruits</li>
                </ul>
            </li>
            <li><strong>BOISSON</strong> : Sirop à l'eau ou Jus de pomme</li>
        </ul>
    </div>
    
    <div class="menu-formule">
        <h3>🔥 Menu "GRILL MASTER" - 32.00 €</h3>
        <p class="formule-details"><em>Menu complet disponible le soir et le week-end</em></p>
        <ul>
            <li><strong>ENTRÉE</strong> (au choix) :
                <ul>
                    <li>Onion Rings (Petite portion)</li>
                    <li>OU Os à Moelle</li>
                    <li>OU Œuf Mayo "Grand Miam"</li>
                </ul>
            </li>
            <li><strong>PLAT</strong> (au choix) :
                <ul>
                    <li>Toute la section Burgers</li>
                    <li>Toute la section BBQ (Ribs, Magret)</li>
                    <li>Le Pavé du Chef ou Saumon</li>
                </ul>
                <p><em>Exclusion : Entrecôte XXL +4€ / Côte de Bœuf hors menu</em></p>
            </li>
            <li><strong>DESSERT</strong> (au choix) :
                <ul>
                    <li>Cheesecake, Brioche Perdue ou Coupe de Glace 3 boules</li>
                </ul>
                <p><em>Exclusion : Profiteroles XXL +2€</em></p>
            </li>
            <li><strong>BOISSON INCLUSE</strong> : Pinte de Bière (50cl) ou Soft (50cl)</li>
        </ul>
    </div>
</main>

<footer>
    <p>&copy; 2026 Le Grand Miam - Projet Creative Yumland</p>
</footer>
<script>
    // --- FONCTION AJOUTER AU PANIER EN AJAX ---
    function ajouterAuPanier(id_produit) {
        const formData = new FormData();
        formData.append('id_produit', id_produit);
        formData.append('quantite', 1);

        fetch('/api/ajouter_panier.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Met à jour le compteur du panier en direct sans recharger la page !
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                } else {
                    const cartIcon = document.querySelector('.cart-icon');
                    if (cartIcon) {
                        cartIcon.innerHTML = '🛒 <span class="cart-count">' + data.count + '</span>';
                    }
                }
                alert("😋 Plat ajouté à votre panier avec succès !");
            } else {
                alert("Erreur lors de l'ajout au panier.");
            }
        })
        .catch(err => console.error(err));
    }

    // --- FONCTION DE FILTRE DE RECHERCHE ---
    function applyFilters() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        const selectedCategory = document.getElementById('categoryFilter').value;
        const selectedSpec = document.getElementById('specFilter').value;
        
        document.querySelectorAll('.menu-table').forEach(table => {
            const tableCategory = table.getAttribute('data-category');
            let tableHasVisibleRows = false;
            
            table.querySelectorAll('tbody tr').forEach(row => {
                if (row.classList.contains('separator')) {
                    return;
                }
                
                const text = row.textContent.toLowerCase();
                const specCell = row.querySelector('td[data-spec]');
                const spec = specCell ? specCell.getAttribute('data-spec') : null;
                
                const searchMatch = text.includes(searchQuery);
                const specMatch = (selectedSpec === 'all' || spec === selectedSpec);
                
                if (searchMatch && specMatch) {
                    row.style.display = '';
                    if (selectedCategory === 'all' || selectedCategory === tableCategory) {
                        tableHasVisibleRows = true;
                    }
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (selectedCategory === 'all') {
                table.style.display = tableHasVisibleRows ? '' : 'none';
            } else {
                table.style.display = (selectedCategory === tableCategory && tableHasVisibleRows) ? '' : 'none';
            }
        });
    }
    
    document.addEventListener('DOMContentLoaded', applyFilters);
</script>

</body>
</html>
