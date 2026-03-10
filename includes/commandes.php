<?php
/**
 * Fonctions liées à la gestion des commandes
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/plats.php';

/**
 * Récupère toutes les commandes
 * @param string|null $status Filtre par statut (optionnel)
 * @param int|null $user_id Filtre par utilisateur (optionnel)
 * @return array Liste des commandes
 */
function getAllCommandes($status = null, $user_id = null) {
    $commandes = loadData(COMMANDES_FILE);
    
    // Filtrer par statut si spécifié
    if ($status !== null) {
        $commandes = array_filter($commandes, function($commande) use ($status) {
            return $commande['status'] === $status;
        });
    }
    
    // Filtrer par utilisateur si spécifié
    if ($user_id !== null) {
        $commandes = array_filter($commandes, function($commande) use ($user_id) {
            return $commande['user_id'] == $user_id;
        });
    }
    
    return $commandes;
}

/**
 * Récupère une commande par son ID
 * @param int $id ID de la commande
 * @return array|null Données de la commande ou null si non trouvée
 */
function getCommandeById($id) {
    $commandes = loadData(COMMANDES_FILE);
    
    foreach ($commandes as $commande) {
        if ($commande['id'] == $id) {
            return $commande;
        }
    }
    
    return null;
}

/**
 * Crée une nouvelle commande
 * @param array $commandeData Données de la commande
 * @return bool|int ID de la commande créée ou false en cas d'échec
 */
function createCommande($commandeData) {
    $commandes = loadData(COMMANDES_FILE);
    
    // Générer un nouvel ID
    $newId = 1;
    if (!empty($commandes)) {
        $lastCommande = end($commandes);
        $newId = $lastCommande['id'] + 1;
    }
    
    // Préparer les données de la nouvelle commande
    $commandeData['id'] = $newId;
    $commandeData['date'] = date('Y-m-d\TH:i:s');
    
    // Ajouter la nouvelle commande
    $commandes[] = $commandeData;
    
    // Sauvegarder les données
    if (saveData(COMMANDES_FILE, $commandes)) {
        return $newId;
    }
    
    return false;
}

/**
 * Met à jour le statut d'une commande
 * @param int $id ID de la commande
 * @param string $status Nouveau statut
 * @return bool Succès ou échec
 */
function updateCommandeStatus($id, $status) {
    $commandes = loadData(COMMANDES_FILE);
    
    foreach ($commandes as &$commande) {
        if ($commande['id'] == $id) {
            $commande['status'] = $status;
            return saveData(COMMANDES_FILE, $commandes);
        }
    }
    
    return false;
}

/**
 * Assigne un livreur à une commande
 * @param int $commande_id ID de la commande
 * @param int $livreur_id ID du livreur
 * @return bool Succès ou échec
 */
function assignLivreur($commande_id, $livreur_id) {
    $commandes = loadData(COMMANDES_FILE);
    
    foreach ($commandes as &$commande) {
        if ($commande['id'] == $commande_id) {
            $commande['livreur_id'] = $livreur_id;
            $commande['status'] = 'en livraison';
            return saveData(COMMANDES_FILE, $commandes);
        }
    }
    
    return false;
}

/**
 * Calcule le montant total d'une commande à partir des détails
 * @param array $details Détails de la commande
 * @return float Montant total
 */
function calculateTotal($details) {
    $total = 0;
    
    foreach ($details as $item) {
        $total += $item['prix_unitaire'] * $item['quantite'];
    }
    
    return $total;
}

/**
 * Récupère les commandes assignées à un livreur
 * @param int $livreur_id ID du livreur
 * @return array Liste des commandes
 */
function getCommandesByLivreur($livreur_id) {
    $commandes = loadData(COMMANDES_FILE);
    
    return array_filter($commandes, function($commande) use ($livreur_id) {
        return $commande['livreur_id'] == $livreur_id;
    });
}