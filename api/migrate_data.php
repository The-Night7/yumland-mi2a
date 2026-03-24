<?php
/**
 * api/migrate_data.php
 * Migration intégrale des données JSON vers SQLite
 */
require_once __DIR__ . '/includes/config.php';

$jsonPath = __DIR__ . '/../data/plats.json'; 

if (!file_exists($jsonPath)) {
    die("Erreur : Le fichier source data/plats.json est introuvable.");
}

$plats = json_decode(file_get_contents($jsonPath), true);

try {
    // Début de la transaction pour la performance
    $pdo->beginTransaction();

    // Nettoyage de la table avant import
    $pdo->exec("DELETE FROM Produits");

    $stmt = $pdo->prepare("INSERT INTO Produits (nom, categorie, prix, image_url) VALUES (?, ?, ?, ?)");

    foreach ($plats as $plat) {
        $stmt->execute([
            $plat['nom'],
            $plat['categorie'] ?? 'Plat',
            $plat['prix'],
            $plat['image'] ?? ''
        ]);
    }

    // Validation de toutes les insertions
    $pdo->commit();

    echo "<h2>✅ Migration terminée avec succès</h2>";
    echo "<p>Nombre de produits importés : " . count($plats) . "</p>";
    echo "<p>Votre base de données est maintenant la seule source de vérité.</p>";

} catch (Exception $e) {
    // En cas d'erreur, on annule tout
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "❌ Erreur lors de la migration : " . $e->getMessage();
}
?>