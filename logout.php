<?php
// logout.php

// Cargar el sistema de autenticación
$authPath = __DIR__ . '/partials/auth/auth.php';
if (!is_file($authPath)) {
    die("No se encontró auth.php en: $authPath");
}
require_once $authPath;

// Cerrar sesión del usuario
do_logout();

// Calcular la base del proyecto dinámicamente (ej. /ecommerce)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') {
    $base = '';
}

// Redirigir al inicio
header('Location: ' . $base . '/index.php');
exit;

