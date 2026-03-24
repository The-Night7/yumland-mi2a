<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM Produits");
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($produits);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}