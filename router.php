<?php
// router.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Absolute Pfade sauber bauen (WINDOWS-SAFE)
$publicDir = realpath(__DIR__ . '/src/Public');
$file = $publicDir . $uri;

// Statische Dateien direkt ausliefern
if ($uri !== '/' && is_file($file)) {
    return false;
}

if ($uri === '/') {
    require_once $publicDir . '/index.php';
    return true;
}

http_response_code(404);
return true;
