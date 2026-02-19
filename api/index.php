<?php
// On définit le fuseau horaire
date_default_timezone_set('Europe/Paris');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon PHP sur Vercel</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f0f0; }
        .card { background: white; padding: 2rem; border-radius: 10px; shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .status { color: #0070f3; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 PHP est en ligne !</h1>
    <p>Nous sommes le : <strong><?php echo date('d/m/Y H:i:s'); ?></strong></p>
    <p class="status">Hébergé sur Vercel</p>
    <hr>
</div>
</body>
</html>