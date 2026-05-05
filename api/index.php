<?php
$currentPage = 'home';
require_once __DIR__ . '/includes/header.php';
?>

<section id="hero">
    <div class="container flex-row hero-container">
        <div class="hero-content">
            <h1>Grand par le goût, géant par l'appétit.</h1>
            <p>Le spécialiste de la viande grillée et des burgers XXL.</p>
            <a href="/api/pages/carte.php" class="cta-button">Voir la Carte</a>
        </div>
    </div>
</section>

<section id="presentation">
    <div class="container flex-row">

        <article class="concept-text">
            <h2>L'Esprit Steakhouse</h2>
            <p>Chez <strong>Le Grand Miam</strong>, nous ne faisons pas dans la demi-mesure. Fondé en 2015 par des passionnés de barbecue, notre restaurant vous propose des viandes maturées et grillées à la flamme sous vos yeux.</p>
            <p>Que vous soyez en famille ou entre amis, venez partager un moment convivial autour d'une planche généreuse.</p>
        </article>

        <aside class="info-box card-style">
            <h3><i class="fas fa-bullhorn"></i> L'Info du Chef</h3>
            <p>Ce mois-ci, découvrez notre nouvelle sauce <em>"Fumée du Texas"</em> offerte avec tous les burgers !</p>
        </aside>
    </div>
</section>

<!-- Zone de recherche (Style App Mobile) -->
<section id="home-search" class="container home-search-section">
    <div class="search-wrapper">
        <input type="text" id="homeSearchInput" class="search-input" placeholder="Rechercher un plat (ex: Burger, Entrecôte...)" onkeyup="filterDishes()">
    </div>
</section>

<section id="galerie">
    <div class="container">
        <h2 class="gallery-title"><i class="fas fa-star"></i> Nos Incontournables</h2>

        <!-- Message si aucun résultat -->
        <div id="no-results" class="no-results-msg">
            <p>Aucun plat ne correspond à votre recherche. <i class="fas fa-frown"></i></p>
        </div>

        <div class="gallery-grid" id="dish-gallery">
            <figure class="card-style">
                <img src="/images/nourriture/entrecote.png" alt="Entrecôte grillée 300g">
                <figcaption>L'Entrecôte Royale</figcaption>
            </figure>
            <figure class="card-style">
                <img src="/images/nourriture/burger.png" alt="Burger Double Steak">
                <figcaption>Le Grand Miam Burger</figcaption>
            </figure>
            <figure class="card-style">
                <img src="/images/nourriture/pave.png" alt="Pavé du Chef">
                <figcaption>Le Pavé du Chef</figcaption>
            </figure>
            <figure class="card-style">
                <img src="/images/nourriture/cheesytower.png" alt="Cheesy Tower">
                <figcaption>Le Cheesy Tower</figcaption>
            </figure>
            <figure class="card-style">
                <img src="/images/nourriture/profiteroles.png" alt="Profiteroles géantes">
                <figcaption>Profiteroles XXL</figcaption>
            </figure>
        </div>
    </div>
</section>

<?php
// Le footer se chargera de fermer la balise <main> et d'inclure les scripts JS.
// La logique de recherche a été déplacée dans un fichier JS dédié pour la propreté.
$additionalJs = ['/public/js/home_search.js']; 
require_once __DIR__ . '/includes/footer.php';
?>