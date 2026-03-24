<?php
// router.php : Simule EXACTEMENT le vercel.json pour le serveur PHP local
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// 1. Rediriger l'accueil (/) vers api/index.php
if ($path === '/') {
    require __DIR__ . '/api/index.php';
    return true;
}

// 2. Simuler le raccourci Vercel pour le CSS (/css/ -> /public/css/)
if (preg_match('/^\/css\/(.*)$/', $path, $matches)) {
    $file = __DIR__ . '/public/css/' . $matches[1];
    if (file_exists($file)) {
        header('Content-Type: text/css');
        readfile($file);
        return true;
    }
}

// 3. Simuler le raccourci Vercel pour le JS (/js/ -> /public/js/)
if (preg_match('/^\/js\/(.*)$/', $path, $matches)) {
    $file = __DIR__ . '/public/js/' . $matches[1];
    if (file_exists($file)) {
        header('Content-Type: application/javascript');
        readfile($file);
        return true;
    }
}

// 4. NOUVEAU : Rediriger intelligemment les clics vers le dossier /api/
// Si on clique sur un lien vers un fichier .php (ex: /pages/carte.php)
if (preg_match('/^\/(.*\.php)$/', $path, $matches)) {
    $chemin_demande = $matches[1];
    
    // Si le lien ne contient pas déjà "api/", on l'ajoute virtuellement
    if (strpos($chemin_demande, 'api/') !== 0) {
        $file = __DIR__ . '/api/' . $chemin_demande;
    } else {
        $file = __DIR__ . '/' . $chemin_demande;
    }
    
    // Si le fichier existe bien, on l'affiche
    if (file_exists($file)) {
        require $file;
        return true;
    }
}

// 5. Laisse le serveur PHP gérer tout le reste normalement (images, polices, etc.)
return false;