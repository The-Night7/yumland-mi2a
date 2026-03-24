<?php
/**
 * api/commander.php
 * Calcul du montant total et redirection vers l'interface de paiement CYBank.
 */

require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: ../public/html/carte.html?error=panier_vide');
    exit;
}

$total = 0;
$ids = array_keys($_SESSION['panier']);
$placeholder = implode(',', array_fill(0, count($ids), '?'));

// On récupère les prix réels depuis la base SQL pour éviter la fraude
$stmt = $pdo->prepare("SELECT id_produit, prix FROM Produits WHERE id_produit IN ($placeholder)");
$stmt->execute($ids);
$produits = $stmt->fetchAll();

foreach ($produits as $produit) {
    $quantite = $_SESSION['panier'][$produit['id_produit']];
    $total += $produit['prix'] * $quantite;
}

// Paramètres pour CYBank (v1.1)
$vendeur = "MI-2_A";
$session = session_id(); // Identifiant de session unique
$url_cybank = "https://www.plateforme-smc.fr/cybank/";

// Construction de l'URL de redirection vers CYBank
$query = http_build_query([
    'vendeur' => $vendeur,
    'montant' => $total,
    'session' => $session,
    'url_retour' => "http://localhost:8000/api/retour_paiement.php"
]);

header("Location: " . $url_cybank . "?" . $query);
exit;