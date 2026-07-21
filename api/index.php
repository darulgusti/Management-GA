<?php
// Set working directory to project root for relative includes
chdir(__DIR__ . '/..');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = ltrim($uri, '/');

// Route root / to index.php
if (empty($path)) {
    require __DIR__ . '/../index.php';
    exit;
}

$target = __DIR__ . '/../' . $path;

// Route existing PHP files (e.g., dashboard.php, login.php)
if (file_exists($target) && is_file($target)) {
    require $target;
    exit;
}

// Route extensionless requests (e.g., /dashboard -> dashboard.php)
if (file_exists($target . '.php') && is_file($target . '.php')) {
    require $target . '.php';
    exit;
}

// Fallback: 404
http_response_code(404);
echo "404 Page Not Found";
