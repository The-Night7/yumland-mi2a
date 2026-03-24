<?php
// router.php : Simule le comportement de vercel.json pour le serveur PHP local
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

// Laisse le serveur PHP gérer tout le reste normalement (les images dans /public, etc.)
return false;