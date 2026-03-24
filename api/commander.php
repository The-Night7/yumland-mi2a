<?php
/**
 * api/commander.php
<<<<<<< HEAD
 * Calcul du montant total et redirection vers l'interface de paiement CYBank.
 */

=======
 * Calcule le total réel via SQL et redirige vers CYBank.
 */
>>>>>>> 6518598e92383c1a044d45be77e48db623dcf586
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: ../public/html/carte.html?error=panier_vide');
    exit;
}

$total = 0;
<<<<<<< HEAD
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
=======
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

>>>>>>> 6518598e92383c1a044d45be77e48db623dcf586
$query = http_build_query([
    'vendeur' => $vendeur,
    'montant' => $total,
    'session' => $session,
<<<<<<< HEAD
    'url_retour' => "http://localhost:8000/api/retour_paiement.php"
]);

header("Location: " . $url_cybank . "?" . $query);
exit;
=======
    'url_retour' => $url_retour
]);

// Redirection vers l'interface officielle
header("Location: https://www.plateforme-smc.fr/cybank/?" . $query);
exit;

?>
>>>>>>> 6518598e92383c1a044d45be77e48db623dcf586
