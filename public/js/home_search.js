function filterDishes() {
    // 1. Récupérer le texte saisi
    const query = document.getElementById('homeSearchInput').value.toLowerCase();

    // 2. Récupérer toutes les cartes de plats
    const dishes = document.querySelectorAll('.gallery-grid figure');
    let hasResults = false;

    // 3. Boucler sur chaque plat
    dishes.forEach(dish => {
        const title = dish.querySelector('figcaption').textContent.toLowerCase();

        // 4. Vérifier si le titre contient la recherche
        if (title.includes(query)) {
            dish.style.display = ""; // Afficher (reset CSS)
            hasResults = true;
        } else {
            dish.style.display = "none"; // Masquer
        }
    });

    // 5. Gérer le message "Aucun résultat"
    const noResultsMsg = document.getElementById('no-results');
    if (hasResults) {
        noResultsMsg.style.display = 'none';
    } else {
        noResultsMsg.style.display = 'block';
    }
}
