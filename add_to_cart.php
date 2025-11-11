<?php
// add_to_cart.php
require __DIR__ . '/api/db.php';

// (Opcional) Cargar auth.php si existe (en cualquiera de estas rutas).
// No es obligatorio para agregar al carrito.
$authCandidates = [
  __DIR__ . '/partials/auth/auth.php',
  __DIR__ . '/auth/auth.php',
];
foreach ($authCandidates as $authFile) {
  if (is_file($authFile)) { require_once $authFile; break; }
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ===== Base del proyecto para redirecciones seguras =====
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }

// ===== 1) Leer inputs =====
$id = isset($_POST['id_producto']) ? (int)$_POST['id_producto']
   : (isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0);

// Donde regresar (por defecto, checkout)
$redirect = isset($_POST['redirect']) ? (string)$_POST['redirect'] : '';
if (!$redirect && !empty($_SERVER['HTTP_REFERER'])) {
  $redirect = $_SERVER['HTTP_REFERER'];
}
if (!$redirect) {
  $redirect = $base . '/checkout.php';
}

// Sanitizar redirect: solo rutas locales
$host = $_SERVER['HTTP_HOST'] ?? '';
if (preg_match('~^https?://~i', $redirect)) {
  $url = parse_url($redirect);
  if (!$url || !isset($url['host']) || strcasecmp($url['host'], $host) !== 0) {
    $redirect = $base . '/checkout.php';
  } else {
    $redirect = (isset($url['path']) ? $url['path'] : '/') .
                (isset($url['query']) ? '?' . $url['query'] : '');
  }
}

// ===== 2) Validaciones básicas =====
if ($id <= 0) {
  header('Location: ' . $redirect);
  exit;
}

// ===== 3) Validar producto activo y stock =====
$st = $pdo->prepare("SELECT id, stock FROM productos WHERE id = ? AND activo = 1");
$st->execute([$id]);
$p = $st->fetch();

if (!$p || (int)$p['stock'] <= 0) {
  header('Location: ' . $redirect);
  exit;
}

// ===== 4) Carrito en sesión como mapa id => cantidad =====
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$current = (int)($_SESSION['cart'][$id] ?? 0);
$stock   = (int)$p['stock'];
if ($current < $stock) {
  $_SESSION['cart'][$id] = $current + 1;
}

// ===== 5) Regresar a la misma página (o al checkout) =====
header('Location: ' . $redirect);
exit;
