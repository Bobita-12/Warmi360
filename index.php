<?php
// index.php — Front controller to make this repo deployable to PHP web hosts/clouds.
// It routes requests to /public/index.php if present, serves static files when using PHP built-in server,
// and provides a simple /health endpoint for health checks.

$publicDir = __DIR__ . '/public';
$frontController = $publicDir . '/index.php';

// Simple health check
if (isset($_SERVER['REQUEST_URI']) && preg_match('#^/health/?$#', $_SERVER['REQUEST_URI'])) {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'ok', 'app' => 'Warmi360']);
    exit;
}

// If running with PHP built-in server, let it serve static files from public/
if (php_sapi_name() === 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = $publicDir . $url;
    if (is_file($file) && realpath($file) !== realpath(__FILE__)) {
        return false; // serve the requested resource as-is
    }
}

// If public/index.php exists, forward the request to it
if (file_exists($frontController)) {
    // Prevent accidental exposure of .env or other sensitive files
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '.env') !== false) {
        http_response_code(403);
        exit('Forbidden');
    }
    require $frontController;
    exit;
}

// Fallback: minimal info page (do not reveal secrets)
header('Content-Type: text/plain; charset=utf-8');
echo "Warmi360 - Plataforma Sorora para Mujeres\n";
echo "No public/index.php found. To deploy this app, make sure the 'public' directory contains a front controller.\n";
echo "Available root files:\n";
$files = array_diff(scandir(__DIR__), ['.','..']);
foreach ($files as $f) {
    if ($f === '.env') { continue; }
    echo "- $f\n";
}
exit;
?>