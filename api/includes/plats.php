<?php
/**
 * Fonctions liées à la gestion des plats et des menus
 */
require_once __DIR__ . '/config.php';

/**
 * Récupère tous les plats
 * @param string|null $categorie Filtre par catégorie (optionnel)
 * @param int|null $menu_id Filtre par menu (optionnel)
 * @return array Liste des plats
 */
function getAllPlats($categorie = null, $menu_id = null) {
    $plats = loadData(PLATS_FILE);
    
    // Filtrer par catégorie si spécifiée
    if ($categorie !== null) {
        $plats = array_filter($plats, function($plat) use ($categorie) {
            return $plat['categorie'] === $categorie;
        });
    }
    
    // Filtrer par menu si spécifié
    if ($menu_id !== null) {
        $plats = array_filter($plats, function($plat) use ($menu_id) {
            return $plat['menu_id'] == $menu_id;
        });
    }
    
    return $plats;
}

/**
 * Récupère un plat par son ID
 * @param int $id ID du plat
 * @return array|null Données du plat ou null si non trouvé
 */
function getPlatById($id) {
    $plats = loadData(PLATS_FILE);
    
    foreach ($plats as $plat) {
        if ($plat['id'] == $id) {
            return $plat;
        }
    }
    
    return null;
}

/**
 * Récupère toutes les catégories de plats
 * @return array Liste des catégories uniques
 */
function getAllCategories() {
    $plats = loadData(PLATS_FILE);
    $categories = [];
    
    foreach ($plats as $plat) {
        if (!in_array($plat['categorie'], $categories)) {
            $categories[] = $plat['categorie'];
        }
    }
    
    sort($categories);
    return $categories;
}

/**
 * Récupère tous les menus
 * @return array Liste des menus
 */
function getAllMenus() {
    return loadData(MENUS_FILE);
}

/**
 * Récupère un menu par son ID
 * @param int $id ID du menu
 * @return array|null Données du menu ou null si non trouvé
 */
function getMenuById($id) {
    $menus = loadData(MENUS_FILE);
    
    foreach ($menus as $menu) {
        if ($menu['id'] == $id) {
            return $menu;
        }
    }
    
    return null;
}

/**
 * Recherche des plats par nom
 * @param string $query Terme de recherche
 * @return array Liste des plats correspondants
 */
function searchPlats($query) {
    $plats = loadData(PLATS_FILE);
    $results = [];
    
    $query = strtolower($query);
    
    foreach ($plats as $plat) {
        if (strpos(strtolower($plat['nom']), $query) !== false || 
            strpos(strtolower($plat['description']), $query) !== false) {
            $results[] = $plat;
        }
    }
    
    return $results;
}