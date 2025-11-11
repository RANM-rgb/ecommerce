<?php
// login.php

// 1) Cargar auth con ruta absoluta
$authPath = __DIR__ . '/partials/auth/auth.php';
if (!is_file($authPath)) {
    die("No se encontró auth.php en: $authPath");
}
require_once $authPath;

// 2) Base del proyecto (p.ej. "/ecommerce")
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }

// 3) Resolver redirect (POST > GET > home) y sanearlo
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? ($base . '/index.php');

// Sanear redirect: solo permitimos rutas locales dentro del mismo host
$redirect = (string)$redirect;
$host = $_SERVER['HTTP_HOST'] ?? '';
if (preg_match('~^https?://~i', $redirect)) {
    $url = parse_url($redirect);
    if (!$url || !isset($url['host']) || strcasecmp($url['host'], $host) !== 0) {
        $redirect = $base . '/index.php';
    } else {
        // reconstruye path+query local
        $redirect = (isset($url['path']) ? $url['path'] : '/') .
                    (isset($url['query']) ? '?' . $url['query'] : '');
    }
} elseif ($redirect === '' || $redirect[0] !== '/') {
    // Forzar a ruta absoluta local
    $redirect = $base . '/index.php';
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (attempt_login($email, $pass)) {
        header('Location: ' . $redirect);
        exit;
    }
    $err = "Credenciales inválidas o cuenta sin activar.";
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="theme-dark">
  <h1>Iniciar sesión</h1>

  <?php if ($err): ?>
    <div class="alert error"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <form method="post" class="form">
    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
    <label>Email
      <input type="email" name="email" required>
    </label>
    <label>Contraseña
      <input type="password" name="password" required>
    </label>
    <button class="btn btn-accent">Entrar</button>
  </form>

  <p>¿Sin cuenta? <a href="register.php">Regístrate</a></p>
</body>
</html>

