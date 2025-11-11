<?php
// register.php

// 1) Cargar la librería de auth
$authPath = __DIR__ . '/partials/auth/auth.php';
if (!is_file($authPath)) {
    die("No se encontró auth.php en: $authPath");
}
require_once $authPath;

// 2) Base del proyecto (ej. "/ecommerce")
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }

$err = $ok = '';

// 3) Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre'] ?? '');
  $email  = trim($_POST['email'] ?? '');
  $pass   = $_POST['password']  ?? '';
  $pass2  = $_POST['password2'] ?? '';

  if (!$nombre || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6 || $pass !== $pass2) {
    $err = "Revisa los datos (contraseña mínima 6 y deben coincidir).";
  } else {
    // email único
    $st = $pdo->prepare("SELECT 1 FROM usuarios WHERE email=? LIMIT 1");
    $st->execute([$email]);
    if ($st->fetch()) {
      $err = "Ese email ya está registrado.";
    } else {
      $hash  = password_hash($pass, PASSWORD_DEFAULT);
      $token = bin2hex(random_bytes(16));

      $ins = $pdo->prepare("
        INSERT INTO usuarios (email, password_hash, nombre, is_active, verification_token)
        VALUES (?, ?, ?, 0, ?)
      ");
      $ins->execute([$email, $hash, $nombre, $token]);
      $uid = (int)$pdo->lastInsertId();

      log_auth('register', $uid);

      // Enlace de verificación (pendiente envío real de correo)
      $link = $base . '/verify.php?token=' . $token . '&email=' . urlencode($email);
      $ok = 'Registro creado. Verifica tu cuenta con este enlace: 
             <a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($link) . '</a>';
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Registro</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="theme-dark">
  <h1>Crear cuenta</h1>

  <?php if ($err): ?>
    <div class="alert error"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <?php if ($ok): ?>
    <div class="alert success"><?= $ok ?></div>
  <?php endif; ?>

  <form method="post" class="form">
    <label>Nombre
      <input name="nombre" value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>" required>
    </label>
    <label>Email
      <input type="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
    </label>
    <label>Contraseña
      <input type="password" name="password" required>
    </label>
    <label>Repite contraseña
      <input type="password" name="password2" required>
    </label>
    <button class="btn btn-accent">Registrarme</button>
  </form>

  <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
</body>
</html>
