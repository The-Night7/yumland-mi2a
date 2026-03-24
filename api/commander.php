<?php
/**
 * api/commander.php
 * Calcule le total réel via SQL et redirige vers CYBank.
 */
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: ../public/html/carte.html?error=panier_vide');
    exit;
}

$total = 0;
// On boucle sur le panier (ID => Quantité)
foreach ($_SESSION['panier'] as $id => $qte) {
    $stmt = $pdo->prepare("SELECT prix FROM Produits WHERE id_produit = ?");
    $stmt->execute([$id]);
    $prix = $stmt->fetchColumn();
    $total += $prix * $qte;
}

// Paramètres CYBank
$vendeur = "MI-2_A";
$session = session_id();
$url_retour = "http://localhost:8000/api/retour_paiement.php";

$query = http_build_query([
    'vendeur' => $vendeur,
    'montant' => $total,
    'session' => $session,
    'url_retour' => $url_retour
]);

// Redirection vers l'interface officielle
header("Location: https://www.plateforme-smc.fr/cybank/?" . $query);
exit;

?>