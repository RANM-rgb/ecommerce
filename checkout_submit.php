<?php
require __DIR__ . '/api/db.php';

/* Cargar auth robusto */
$root  = __DIR__;
$auth1 = $root . '/auth/auth.php';
$auth2 = $root . '/partials/auth/auth.php';
if (is_file($auth1))      require_once $auth1;
elseif (is_file($auth2))  require_once $auth2;

if (session_status() === PHP_SESSION_NONE) session_start();
require_login('/ecommerce/checkout_submit.php');

try {
  $userId = (int)($_SESSION['user']['id'] ?? 0);
  if (!$userId) throw new RuntimeException('Usuario no autenticado.');

  /* Carrito normalizado */
  $sessionCart = $_SESSION['cart'] ?? $_SESSION['carrito'] ?? $_SESSION['cart_items'] ?? [];
  $cart = [];
  if ($sessionCart) {
    if (is_array($sessionCart) && $sessionCart && is_numeric(array_key_first($sessionCart))) {
      foreach ($sessionCart as $pid=>$qty){
        $pid=(int)$pid; $qty=max(1,(int)$qty);
        if($pid>0) $cart[$pid]=($cart[$pid]??0)+$qty;
      }
    } else {
      foreach ($sessionCart as $row) {
        if(!is_array($row)) continue;
        $pid= isset($row['product_id'])?(int)$row['product_id']
            :(isset($row['id'])?(int)$row['id']:0);
        $qty= isset($row['cantidad'])?(int)$row['cantidad']
            :(isset($row['qty'])?(int)$row['qty']:1);
        if($pid>0) $cart[$pid]=($cart[$pid]??0)+max(1,$qty);
      }
    }
  }
  if (!$cart) throw new RuntimeException('Tu carrito está vacío.');

  $ids = array_keys($cart);
  $ph  = implode(',', array_fill(0, count($ids), '?'));

  // productos (id o id_producto)
  try {
    $st = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE id IN ($ph) AND activo=1");
    $st->execute($ids);
    $rows = $st->fetchAll();
  } catch (Throwable $e) {
    $st = $pdo->prepare("SELECT id_producto AS id, nombre, precio FROM productos WHERE id_producto IN ($ph) AND activo=1");
    $st->execute($ids);
    $rows = $st->fetchAll();
  }

  $items = [];
  $total = 0.00;
  foreach ($rows as $p) {
    $pid   = (int)$p['id'];
    $qty   = max(1,(int)$cart[$pid]);
    $price = (float)$p['precio'];
    $sub   = $price * $qty;
    $total += $sub;
    $items[] = [
      'producto_id'=>$pid,
      'nombre'     =>$p['nombre'],
      'precio'     =>$price,
      'cantidad'   =>$qty,
      'subtotal'   =>$sub
    ];
  }
  if (!$items) throw new RuntimeException('No se encontraron productos activos.');

  $pdo->beginTransaction();

  /* ====== PEDIDOS: detectar columna de estado ====== */
  $pedidoCols = [];
  foreach ($pdo->query("SHOW COLUMNS FROM pedidos") as $r) $pedidoCols[] = $r['Field'];
  $estadoCol = in_array('estatus',$pedidoCols,true) ? 'estatus'
            : (in_array('status',$pedidoCols,true)  ? 'status'
            : (in_array('estado',$pedidoCols,true)  ? 'estado' : null));

  if ($estadoCol) {
    $insPed = $pdo->prepare("INSERT INTO pedidos (user_id, total, {$estadoCol}) VALUES (?, ?, ?)");
    $insPed->execute([$userId, $total, 'pendiente']);
  } else {
    $insPed = $pdo->prepare("INSERT INTO pedidos (user_id, total) VALUES (?, ?)");
    $insPed->execute([$userId, $total]);
  }
  $pedidoId = (int)$pdo->lastInsertId();

  /* ====== PEDIDO_ITEMS: inserción flexible ====== */
  $piCols = [];
  foreach ($pdo->query("SHOW COLUMNS FROM pedido_items") as $r) $piCols[] = $r['Field'];

  $colPedidoId = in_array('pedido_id',$piCols,true)   ? 'pedido_id'   : (in_array('id_pedido',$piCols,true)   ? 'id_pedido'   : null);
  $colProdId   = in_array('producto_id',$piCols,true) ? 'producto_id' : (in_array('id_producto',$piCols,true) ? 'id_producto' : null);

  // Opcionales: se usan sólo si existen
  $colNombre   = null;
  foreach (['nombre','producto_nombre','nombre_producto','name','titulo','producto'] as $c) {
    if (in_array($c,$piCols,true)) { $colNombre = $c; break; }
  }
  $colPrecio   = null;
  foreach (['precio','precio_unitario','unit_price','price','precio_prod','precio_producto'] as $c) {
    if (in_array($c,$piCols,true)) { $colPrecio = $c; break; }
  }
  $colCantidad = null;
  foreach (['cantidad','qty','unidades','cantidad_producto','cantidad_prod'] as $c) {
    if (in_array($c,$piCols,true)) { $colCantidad = $c; break; }
  }
  $colSubtotal = null;
  foreach (['subtotal','importe_item','total_linea','importe'] as $c) {
    if (in_array($c,$piCols,true)) { $colSubtotal = $c; break; }
  }

  if (!$colPedidoId || !$colProdId) {
    throw new RuntimeException(
      "Revisa columnas de pedido_items. Se requieren al menos: pedido_id/id_pedido y producto_id/id_producto."
    );
  }

  // Armamos UNA sola sentencia con las columnas disponibles
  $cols = [$colPedidoId, $colProdId];
  if ($colNombre)   $cols[] = $colNombre;
  if ($colPrecio)   $cols[] = $colPrecio;
  if ($colCantidad) $cols[] = $colCantidad;
  if ($colSubtotal) $cols[] = $colSubtotal;

  $placeholders = '(' . rtrim(str_repeat('?,', count($cols)), ',') . ')';
  $sqlInsertPI  = "INSERT INTO pedido_items (" . implode(',', $cols) . ") VALUES {$placeholders}";
  $insPI = $pdo->prepare($sqlInsertPI);

  foreach ($items as $it) {
    $params = [$pedidoId, $it['producto_id']];
    if ($colNombre)   $params[] = $it['nombre'];
    if ($colPrecio)   $params[] = $it['precio'];
    if ($colCantidad) $params[] = $it['cantidad'];
    if ($colSubtotal) $params[] = $it['subtotal'];
    $insPI->execute($params);
  }

  /* ====== PAGOS_SIMULADOS ====== */
  $pgCols = [];
  foreach ($pdo->query("SHOW COLUMNS FROM pagos_simulados") as $r) $pgCols[] = $r['Field'];
  if (!in_array('pedido_id',$pgCols,true)) throw new RuntimeException("La tabla pagos_simulados debe tener pedido_id.");

  $map = [
    'metodo'       => in_array('metodo',$pgCols,true) ? 'metodo' : (in_array('metodo_pago',$pgCols,true) ? 'metodo_pago' : null),
    'monto'        => in_array('monto',$pgCols,true) ? 'monto' : (in_array('importe',$pgCols,true) ? 'importe' : null),
    'moneda'       => in_array('moneda',$pgCols,true) ? 'moneda' : (in_array('currency',$pgCols,true) ? 'currency' : null),
    'status'       => in_array('status',$pgCols,true) ? 'status' : (in_array('estado',$pgCols,true) ? 'estado' : null),
    'tx_reference' => in_array('tx_reference',$pgCols,true) ? 'tx_reference' : (in_array('referencia',$pgCols,true) ? 'referencia' : null),
  ];
  foreach ($map as $k=>$v) if ($v===null) throw new RuntimeException("Falta columna para '$k' en pagos_simulados.");

  $metodo = 'paypal_sim'; $moneda = 'MXN'; $status = 'pendiente'; $tx = substr(md5(uniqid('',true)),0,12);
  $ps = $pdo->prepare("INSERT INTO pagos_simulados (pedido_id, {$map['metodo']}, {$map['monto']}, {$map['moneda']}, {$map['status']}, {$map['tx_reference']}) VALUES (?,?,?,?,?,?)");
  $ps->execute([$pedidoId,$metodo,$total,$moneda,$status,$tx]);
  $pagoId = (int)$pdo->lastInsertId();

  $pdo->prepare("INSERT INTO pago_eventos (pago_id, event) VALUES (?, 'created')")->execute([$pagoId]);

  $pdo->commit();

  unset($_SESSION['cart'], $_SESSION['carrito'], $_SESSION['cart_items']);
  header('Location: /ecommerce/user/orders.php?ok=1');
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo "<h3>Error en el checkout:</h3><pre>".htmlspecialchars($e->getMessage())."</pre>";
}


