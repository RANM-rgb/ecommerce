<?php
require __DIR__ . '/api/db.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') $base = '';
$pedidoId = (int)($_GET['pedido'] ?? 0);
$tx = $_GET['tx'] ?? '';
$st = $pdo->prepare("SELECT id,total,estatus,created_at FROM pedidos WHERE id=?");
$st->execute([$pedidoId]);
$ped = $st->fetch();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pago recibido</title>
  <link rel="stylesheet" href="<?= $base ?>/styles.css">
  <style>.wrap{max-width:720px;margin:40px auto;padding:20px;border:1px solid var(--gk-border,#1f2937);border-radius:12px}</style>
</head>
<body class="theme-dark">
<div class="wrap">
  <?php if($ped): ?>
    <h1>✅ ¡Pago recibido!</h1>
    <p>Pedido <strong>#<?= (int)$ped['id'] ?></strong> — estatus: <strong><?= htmlspecialchars($ped['estatus']) ?></strong></p>
    <p>Total: <strong>$<?= number_format($ped['total'],2) ?></strong></p>
    <?php if($tx): ?><p>Referencia: <code><?= htmlspecialchars($tx) ?></code></p><?php endif; ?>
    <p><a class="gk-btn gk-btn-primary" href="<?= $base ?>/user/orders.php">Ver mis pedidos</a></p>
  <?php else: ?>
    <h1>Pago</h1><p>No se encontró el pedido.</p>
  <?php endif; ?>
</div>
</body>
</html>
