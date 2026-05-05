<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/config.php';
// require_once __DIR__ . '/../includes/plats.php'; // Plus besoin si tout est dans la DB !
// require_once __DIR__ . '/../includes/panier.php';

// Message pour l'ajout au panier
$message = '';
if (isset($_SESSION['cart_message'])) {
    $message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

$currentPage = 'carte';
$pageTitle = 'Notre Carte';

// 1. RÉCUPÉRATION DYNAMIQUE DE TOUS LES PRODUITS
$stmt = $pdo->query("SELECT * FROM Produits ORDER BY id_produit ASC");
$tous_les_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. TRI DES PRODUITS PAR CATÉGORIE
$catalogue = [
    'Entrées' => [],
    'Viandes' => [],
    'Burgers' => [],
    'Desserts' => [],
    'Boissons' => [],
    'Menus' => []
];

foreach ($tous_les_produits as $produit) {
    $cat = $produit['categorie'];
    if (isset($catalogue[$cat])) {
        $catalogue[$cat][] = $produit;
    }
}

// 3. GESTION DES SPÉCIFICITÉS (Vu qu'elles ne sont pas dans la DB, on les associe par l'ID)
$specificites = [
    1  => ['type' => 'vege', 'html' => '<span class="spec-badge spec-vege"><i class="fas fa-leaf"></i> Végétarien</span>'],
    2  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Halal possible</span>'],
    3  => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> Contient Porc</span>'],
    4  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    5  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    6  => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    7  => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> 100% Porc</span>'],
    9  => ['type' => 'poisson', 'html' => '<span class="spec-badge spec-poisson"><i class="fas fa-fish"></i> Option Poisson</span>'],
    10 => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> 100% Porc</span>'],
    11 => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    12 => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    13 => ['type' => 'porc', 'html' => '<span class="spec-badge spec-porc"><i class="fas fa-bacon"></i> (Lardons)</span>'],
    14 => ['type' => 'halal', 'html' => '<span class="spec-badge spec-halal"><i class="fas fa-moon"></i> Option Halal</span>'],
    15 => ['type' => 'vege', 'html' => '<span class="spec-badge spec-vege"><i class="fas fa-leaf"></i> Végétarien</span>'],
];

// Fonction utilitaire pour trouver un produit spécifique (utile pour les Menus)
function getProduitById($id, $produits) {
    foreach ($produits as $p) {
        if ($p['id_produit'] == $id) return $p;
    }
    return null;
}
?>

<div class="menu-container">
    <h1>Notre Carte</h1>
    <p class="intro-text">Steakhouse, Grillades & Burgers XXL - Une expérience culinaire unique</p>
    
    <!-- Barre de recherche et filtre -->
    <div class="search-filter" style="margin-bottom:2rem; display: flex; gap: 1rem; align-items: center;">
        <input type="text" id="searchInput" placeholder="Rechercher un plat…" onkeyup="applyFilters()" class="filter-input" style="flex-grow: 1;">
        <select id="categoryFilter" onchange="applyFilters()" class="filter-input">
            <option value="all">Toutes catégories</option>
            <option value="Entrées">Entrées</option>
            <option value="Viandes">Viandes & Poissons</option>
            <option value="Burgers">Burgers</option>
            <option value="Desserts">Desserts</option>
            <option value="Boissons">Boissons</option>
        </select>
        <select id="specFilter" onchange="applyFilters()" class="filter-input">
            <option value="all">Toutes spécificités</option>
            <option value="halal">Halal</option>
            <option value="porc">Contient du porc</option>
            <option value="vege">Végétarien</option>
            <option value="poisson">Poisson</option>
        </select>
    </div>
    
    <?php
    // Configuration des tableaux pour la boucle dynamique
    $sections = [
        'Entrées' => ['icon' => 'fa-seedling', 'titre' => 'Les Entrées & Partage'],
        'Viandes' => ['icon' => 'fa-fire', 'titre' => 'Le Grill (Viandes & Poissons)'],
        'Burgers' => ['icon' => 'fa-hamburger', 'titre' => 'Les Burgers'],
        'Desserts' => ['icon' => 'fa-ice-cream', 'titre' => 'Desserts (Sweet Ending)'],
        'Boissons' => ['icon' => 'fa-glass-cheers', 'titre' => 'Les Boissons']
    ];

    foreach ($sections as $catKey => $sectionInfo): 
        if (empty($catalogue[$catKey])) continue; // On ignore si la catégorie est vide
    ?>
        <table class="menu-table" data-category="<?= $catKey ?>">
            <caption><i class="fas <?= $sectionInfo['icon'] ?>"></i> <?= $sectionInfo['titre'] ?></caption>
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
            <?php foreach ($catalogue[$catKey] as $produit): 
                $id = $produit['id_produit'];
                $nom = htmlspecialchars($produit['nom'], ENT_QUOTES);
                $desc = htmlspecialchars($produit['description']);
                $prix = number_format($produit['prix'], 2, '.', '') . ' €';
                $options = !empty($produit['options_config']) ? htmlspecialchars($produit['options_config'], ENT_QUOTES) : '';
                
                // Gestion du badge de spécificité
                $specType = isset($specificites[$id]) ? $specificites[$id]['type'] : '';
                $specHtml = isset($specificites[$id]) ? $specificites[$id]['html'] : '';
            ?>
                <tr>
                    <td><?= $nom ?></td>
                    <td><?= $desc ?></td>
                    <td><?= $prix ?></td>
                    <td data-spec="<?= $specType ?>"><?= $specHtml ?></td>
                    <td>
                        <?php if ($options): ?>
                            <button class="btn-primary" data-id="<?= $id ?>" data-nom="<?= $nom ?>" data-options='<?= $options ?>' onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
                        <?php else: ?>
                            <button class="btn-primary" onclick="ajouterAuPanier(<?= $id ?>)"><i class="fas fa-cart-plus"></i> Ajouter</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
    
    <!-- MENUS & FORMULES (Traités séparément pour garder la belle mise en page HTML) -->
    <h2><i class="fas fa-concierge-bell"></i> Nos Menus & Formules</h2>
    
    <?php 
    // Récupération dynamique des menus pour injecter les données de la base dans le design
    $menuLunch = getProduitById(42, $tous_les_produits);
    $menuCowboy = getProduitById(43, $tous_les_produits);
    $menuGrill = getProduitById(44, $tous_les_produits);
    ?>

    <?php if ($menuLunch): ?>
    <div class="menu-formule">
        <h3><i class="fas fa-briefcase"></i> Formule "<?= htmlspecialchars($menuLunch['nom']) ?>" - <?= number_format($menuLunch['prix'], 2) ?> €</h3>
        <p class="formule-details"><em>Disponible uniquement le midi, du lundi au vendredi</em></p>
        <ul>
            <li><strong>PLAT</strong> (au choix) : Burger "Le Grand Miam", Le Pavé du Chef ou Veggie Grill</li>
            <li><strong>BOISSON</strong> : Coca-Cola, Fanta, Sprite (33cl), Verre de vin (12cl) ou Café</li>
        </ul>
        <button class="btn-primary" style="margin-top: 10px;" 
                data-id="<?= $menuLunch['id_produit'] ?>" 
                data-nom="<?= htmlspecialchars($menuLunch['nom'], ENT_QUOTES) ?>" 
                data-options='<?= htmlspecialchars($menuLunch['options_config'], ENT_QUOTES) ?>'
                onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
    </div>
    <?php endif; ?>
    
    <?php if ($menuCowboy): ?>
    <div class="menu-formule">
        <h3><i class="fas fa-hat-cowboy"></i> Menu "<?= htmlspecialchars($menuCowboy['nom']) ?>" - <?= number_format($menuCowboy['prix'], 2) ?> €</h3>
        <p class="formule-details"><em>Pour les enfants de moins de 12 ans</em></p>
        <ul>
            <li><strong>PLAT</strong> : Mini Cheeseburger ou Nuggets de Poulet (x6)</li>
            <li><strong>DESSERT</strong> : Sundae Vanille ou Compote de fruits</li>
            <li><strong>BOISSON</strong> : Sirop à l'eau ou Jus de pomme</li>
        </ul>
        <button class="btn-primary" style="margin-top: 10px;" 
                data-id="<?= $menuCowboy['id_produit'] ?>" 
                data-nom="<?= htmlspecialchars($menuCowboy['nom'], ENT_QUOTES) ?>" 
                data-options='<?= htmlspecialchars($menuCowboy['options_config'], ENT_QUOTES) ?>'
                onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
    </div>
    <?php endif; ?>
    
    <?php if ($menuGrill): ?>
    <div class="menu-formule">
        <h3><i class="fas fa-fire-alt"></i> Menu "<?= htmlspecialchars($menuGrill['nom']) ?>" - <?= number_format($menuGrill['prix'], 2) ?> €</h3>
        <p class="formule-details"><em>Menu complet disponible le soir et le week-end</em></p>
        <ul>
            <li><strong>ENTRÉE</strong> : Onion Rings, Os à Moelle ou Œuf Mayo</li>
            <li><strong>PLAT</strong> : Burgers, BBQ Ribs, Magret, Pavé du Chef ou Saumon</li>
            <li><strong>DESSERT</strong> : Cheesecake, Brioche Perdue ou Coupe de Glace</li>
            <li><strong>BOISSON INCLUSE</strong> : Pinte de Bière (50cl) ou Soft au choix (50cl)</li>
        </ul>
        <button class="btn-primary" style="margin-top: 10px;" 
                data-id="<?= $menuGrill['id_produit'] ?>" 
                data-nom="<?= htmlspecialchars($menuGrill['nom'], ENT_QUOTES) ?>" 
                data-options='<?= htmlspecialchars($menuGrill['options_config'], ENT_QUOTES) ?>'
                onclick="openMenuModal(this)"><i class="fas fa-cart-plus"></i> Ajouter</button>
    </div>
    <?php endif; ?>

</div>

<script>
    // --- FONCTION POUR OUVRIR LA MODAL MENU ---
    function openMenuModal(btn) {
        const id = btn.getAttribute('data-id');
        const nom = btn.getAttribute('data-nom');
        const options = btn.getAttribute('data-options');
        showOptionsModal(id, nom, options);
    }

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
    
    document.addEventListener('DOMContentLoaded', () => {
        applyFilters();
        document.querySelectorAll('.menu-table td').forEach(td => {
            if (td.textContent.includes('€') && !td.querySelector('button')) {
                td.classList.add('price-cell');
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>