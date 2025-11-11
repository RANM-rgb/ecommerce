<?php
// verify.php

// 1) Cargar auth con ruta absoluta (ajustado a /partials/auth/)
$authPath = __DIR__ . '/partials/auth/auth.php';
if (!is_file($authPath)) {
    die("No se encontró auth.php en: $authPath");
}
require_once $authPath;

// 2) Base dinámica (p.ej. "/ecommerce")
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }

$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$msg = "Solicitud inválida.";

if ($token && $email) {
  $st = $pdo->prepare("SELECT id FROM usuarios WHERE email=? AND verification_token=? AND is_active=0");
  $st->execute([$email, $token]);
  if ($row = $st->fetch()) {
    $up = $pdo->prepare("UPDATE usuarios SET is_active=1, verification_token=NULL WHERE id=?");
    $up->execute([(int)$row['id']]);
    log_auth('verify', (int)$row['id']);
    $msg = "Cuenta verificada. Ahora puedes iniciar sesión.";
  } else {
    $msg = "El token no es válido o la cuenta ya está activa.";
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Verificación</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="theme-dark">
  <h1>Verificación de cuenta</h1>
  <div class="alert"><?= htmlspecialchars($msg) ?></div>
  <p><a class="btn" href="<?= $base ?>/login.php">Ir a iniciar sesión</a></p>
</body>
</html>

