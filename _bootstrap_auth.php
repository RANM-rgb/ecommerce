<?php
// _bootstrap_auth.php — Cargador robusto para auth.php SIN imprimir nada

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$docroot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$baseDir = str_replace('\\','/', __DIR__);

// Rutas candidatas donde podría estar auth.php
$try = [
  $baseDir . '/auth/auth.php',
  $baseDir . '/partials/auth/auth.php',
  $docroot . '/ecommerce/auth/auth.php',
  $docroot . '/ecommerce/partials/auth/auth.php',
];

$loaded = false;
foreach ($try as $p) {
  if (is_file($p)) {
    require_once $p;     // <-- este auth.php ya puede incluir init.php si quiere
    $loaded = true;
    break;
  }
}

if (!$loaded) {
  // No imprimas “Conexión exitosa...” ni nada antes de esto o se cortará el HTML.
  header('Content-Type: text/plain; charset=utf-8');
  echo "No se encontró auth.php. Se intentó en:\n\n" . implode("\n", $try);
  exit;
}

