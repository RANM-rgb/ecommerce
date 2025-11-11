<?php
require __DIR__ . '/api/db.php';

/* --- Cargar auth robusto --- */
$root  = __DIR__;
$auth1 = $root . '/auth/auth.php';
$auth2 = $root . '/partials/auth/auth.php';
if (is_file($auth1))      require_once $auth1;
elseif (is_file($auth2))  require_once $auth2;

if (session_status() === PHP_SESSION_NONE) session_start();
require_login('/ecommerce/checkout.php');

/* Ruta base (para rutas relativas correctas si el proyecto vive en /ecommerce) */
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'); if ($base === '/' || $base === '\\') $base = '';

/* Normalizar carrito de sesión */
$sessionCart = $_SESSION['cart'] ?? $_SESSION['carrito'] ?? $_SESSION['cart_items'] ?? [];
$cart = []; // [product_id => qty]
if ($sessionCart) {
  if (is_array($sessionCart) && $sessionCart && is_numeric(array_key_first($sessionCart))) {
    foreach ($sessionCart as $pid=>$qty) { $pid=(int)$pid; $qty=max(1,(int)$qty); if($pid>0) $cart[$pid]=($cart[$pid]??0)+$qty; }
  } else {
    foreach ($sessionCart as $row) {
      if (!is_array($row)) continue;
      $pid = isset($row['product_id'])?(int)$row['product_id']:(isset($row['id'])?(int)$row['id']:0);
      $qty = isset($row['cantidad'])?(int)$row['cantidad']:(isset($row['qty'])?(int)$row['qty']:1);
      if ($pid>0) $cart[$pid]=($cart[$pid]??0)+max(1,$qty);
    }
  }
}

$items = [];
$subtotal = 0.00;

if ($cart) {
  $ids = array_keys($cart);
  $ph  = implode(',', array_fill(0, count($ids), '?'));

  // intenta con id, y si no, con id_producto
  try {
    $st = $pdo->prepare("SELECT id, nombre, precio, imagen FROM productos WHERE id IN ($ph) AND activo=1");
    $st->execute($ids);
    $rows = $st->fetchAll();
  } catch (Throwable $e) {
    $st = $pdo->prepare("SELECT id_producto AS id, nombre, precio, imagen FROM productos WHERE id_producto IN ($ph) AND activo=1");
    $st->execute($ids);
    $rows = $st->fetchAll();
  }

  foreach ($rows as $p) {
    $pid = (int)$p['id'];
    $qty = max(1,(int)($cart[$pid] ?? 1));
    $price = (float)$p['precio'];
    $sub   = $price * $qty;
    $subtotal += $sub;
    $img = $p['imagen'];
    if (!preg_match('/^https?:/i',$img) && stripos($img,'img/')!==0) $img = 'img/'.ltrim($img,'/');
    $items[] = [
      'id'=>$pid,
      'nombre'=>$p['nombre'],
      'precio'=>$price,
      'qty'=>$qty,
      'sub'=>$sub,
      'img'=>$img
    ];
  }
}

$shipping = $items ? 99.00 : 0.00; // demo
$total = $subtotal + $shipping;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Checkout</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= $base ?>/styles.css">
  <style>
    :root{--bg:#0b1220;--card:#111a2b;--muted:#a3acc2;--text:#eef2ff;--accent:#f5c253;--accent-2:#7c3aed;--ok:#10b981;--danger:#ef4444;border:0}
    body{background:var(--bg);color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Arial}
    .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
    .grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}
    .card{background:var(--card);border:1px solid #1f2a44;border-radius:14px;padding:18px}
    h1{font-size:28px;margin:6px 0 18px}
    .label{font-size:14px;color:var(--muted);margin-bottom:6px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    input[type=text]{width:100%;padding:10px;background:#0e172a;border:1px solid #233253;border-radius:10px;color:var(--text)}
    .pay-group{margin-top:14px;display:grid;gap:6px;color:var(--muted)}
    .summary h3{margin-top:0}
    .line{display:flex;gap:12px;margin:10px 0;align-items:center}
    .thumb{width:60px;height:60px;border-radius:10px;object-fit:cover;border:1px solid #24324f}
    .price{margin-left:auto;color:#cfe1ff}
    .muted{color:var(--muted)}
    .total{display:flex;justify-content:space-between;font-weight:700;padding-top:8px;border-top:1px dashed #2a3a66}
    .btn{background:var(--accent-2);border:0;padding:10px 14px;border-radius:10px;color:#fff;cursor:pointer}
    .btn:disabled{opacity:.5;cursor:not-allowed}
    @media (max-width:900px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Checkout</h1>

    <?php if (empty($items)): ?>
      <div class="card">
        <p class="muted">Tu carrito está vacío.</p>
        <p><a class="btn" href="<?= $base ?>/productos.php">Ir a productos</a></p>
      </div>
    <?php else: ?>
      <div class="grid">
        <!-- Datos envío + método pago -->
        <div class="card">
          <h3 style="margin-top:0">Datos de envío</h3>
          <div class="row" style="grid-template-columns:1fr 1fr">
            <div>
              <div class="label">Dirección</div>
              <input type="text" placeholder="Calle y número">
            </div>
            <div>
              <div class="label">Ciudad</div>
              <input type="text" placeholder="Ciudad">
            </div>
            <div>
              <div class="label">Estado</div>
              <input type="text" placeholder="Estado">
            </div>
            <div>
              <div class="label">C.P.</div>
              <input type="text" placeholder="Código postal">
            </div>
          </div>

          <h3 style="margin:18px 0 8px">Método de pago</h3>
          <div class="pay-group">
            <label><input type="radio" name="pm" checked> Tarjeta (simulado)</label>
            <label><input type="radio" name="pm"> PayPal (simulado)</label>
          </div>

          <form action="<?= $base ?>/checkout_submit.php" method="post" style="margin-top:16px">
            <button class="btn">Pagar ahora</button>
          </form>
        </div>

        <!-- Resumen -->
        <aside class="card summary">
          <h3>Resumen</h3>
          <?php foreach ($items as $it): ?>
            <div class="line">
              <img class="thumb" src="<?= htmlspecialchars($it['img']) ?>" alt="" onerror="this.src='<?= $base ?>/img/placeholder.jpg'">
              <div>
                <div><?= htmlspecialchars($it['nombre']) ?> × <?= (int)$it['qty'] ?></div>
                <div class="muted">$<?= number_format($it['precio'],2) ?> c/u</div>
              </div>
              <div class="price">$<?= number_format($it['sub'],2) ?></div>
            </div>
          <?php endforeach; ?>

          <div class="line"><span class="muted">Subtotal</span><span class="price">$<?= number_format($subtotal,2) ?></span></div>
          <div class="line"><span class="muted">Envío</span><span class="price">$<?= number_format($shipping,2) ?></span></div>
          <div class="total"><span>Total</span><span>$<?= number_format($total,2) ?></span></div>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>


