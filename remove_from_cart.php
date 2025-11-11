<?php
// remove_from_cart.php
session_start();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }

$id = isset($_POST['id_producto']) ? (int)$_POST['id_producto']
   : (isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0);

$redirect = isset($_POST['redirect']) ? (string)$_POST['redirect'] : ($base . '/checkout.php');

if (isset($_SESSION['cart'][$id])) {
  unset($_SESSION['cart'][$id]);
}

header('Location: ' . $redirect);
exit;

