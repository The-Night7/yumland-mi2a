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
function getAllCommandes($status = null, $user_id = null, $order = 'ASC') {
    global $pdo;
    $query = "SELECT * FROM Commandes WHERE 1=1";
    $params = [];
    
    if ($status !== null) {
        $query .= " AND statut = ?";
        $params[] = $status;
    }
    
    if ($user_id !== null) {
        $query .= " AND id_client = ?";
        $params[] = $user_id;
    }
    
    // Ajout du tri par date
    if (strtoupper($order) === 'DESC') {
        $query .= " ORDER BY date_commande DESC";
    } else {
        $query .= " ORDER BY date_commande ASC";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Récupère une commande par son ID
 * @param int $id ID de la commande
 * @return array|null Données de la commande ou null si non trouvée
 */
function getCommandeById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM Commandes WHERE id_commande = ?");
    $stmt->execute([$id]);
    $commande = $stmt->fetch();
    return $commande ?: null;
}

/**
 * Crée une nouvelle commande
 * @param array $commandeData Données de la commande
 * @return bool|int ID de la commande créée ou false en cas d'échec
 */
function createCommande($commandeData) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO Commandes (id_client, prix_total, statut, adresse_livraison) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $commandeData['user_id'],
            $commandeData['total'],
            $commandeData['status'] ?? 'En attente',
            $commandeData['adresse'] ?? ''
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Met à jour le statut d'une commande
 * @param int $id ID de la commande
 * @param string $status Nouveau statut
 * @return bool Succès ou échec
 */
function updateCommandeStatus($id, $status) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE Commandes SET statut = ? WHERE id_commande = ?");
        return $stmt->execute([$status, $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Assigne un livreur à une commande
 * @param int $commande_id ID de la commande
 * @param int $livreur_id ID du livreur
 * @return bool Succès ou échec
 */
function assignLivreur($commande_id, $livreur_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE Commandes SET statut = 'En livraison', id_livreur = ? WHERE id_commande = ?");
        return $stmt->execute([$livreur_id, $commande_id]);
    } catch (PDOException $e) {
        return false;
    }
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
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM Commandes WHERE statut = 'En livraison' AND id_livreur = ?");
        $stmt->execute([$livreur_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}