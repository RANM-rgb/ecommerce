<?php
// productos.php
require __DIR__ . '/api/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$title = 'Todos los productos';

// Base para rutas /ecommerce
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }

// Paginación simple (opcional)
$porPagina = 12;
$pagina    = max(1, (int)($_GET['p'] ?? 1));
$offset    = ($pagina - 1) * $porPagina;

// Contador total
$total = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn();
$paginas = max(1, (int)ceil($total / $porPagina));

// Trae todos los productos activos (sin filtrar por categoría)
$sql = "SELECT 
          id AS id_producto, 
          nombre, 
          descripcion, 
          precio, 
          imagen, 
          stock
        FROM productos
        WHERE activo = 1
        ORDER BY id DESC
        LIMIT :lim OFFSET :off";

$st = $pdo->prepare($sql);
$st->bindValue(':lim', $porPagina, PDO::PARAM_INT);
$st->bindValue(':off', $offset, PDO::PARAM_INT);
$st->execute();
$items = $st->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= $base ?>/styles.css">
</head>

<?php include __DIR__ . '/partials/geekerath_layout.php'; ?>
<body class="theme-dark">

<?php include __DIR__ . '/partials/header.php'; ?>

<section class="container">
  <h1 class="section-title"><?= htmlspecialchars($title) ?></h1>

  <?php if (!$items): ?>
    <p class="muted">No hay productos para mostrar.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($items as $p):
        // normaliza imagen
        $img = (string)$p['imagen'];
        if (!preg_match('/^https?:/i',$img) && stripos($img,'img/')!==0) {
          $img = 'img/' . ltrim($img,'/');
        }
        $img = $base . '/' . ltrim($img,'/');
      ?>
        <article class="card">
          <div class="card__media">
            <img src="<?= htmlspecialchars($img) ?>"
                 alt="<?= htmlspecialchars($p['nombre']) ?>"
                 onerror="this.src='<?= $base ?>/img/placeholder.jpg'">
          </div>
          <div class="card__body">
            <h3 class="card__title"><?= htmlspecialchars($p['nombre']) ?></h3>
            <p class="card__desc"><?= htmlspecialchars($p['descripcion']) ?></p>
            <div class="card__meta">
              <span class="price">$<?= number_format((float)$p['precio'], 2) ?></span>
              <span class="stock <?= ((int)$p['stock']>0 ? 'ok' : 'out') ?>">
                <?= ((int)$p['stock']>0 ? ('Disp: ' . (int)$p['stock']) : 'Agotado') ?>
              </span>
            </div>
          </div>
          <form class="card__action" action="<?= $base ?>/add_to_cart.php" method="post">
            <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
            <button class="btn btn-accent" <?= ((int)$p['stock']>0 ? '' : 'disabled') ?>>
              Agregar al carrito
            </button>
          </form>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if ($paginas > 1): ?>
      <nav class="pager" style="margin-top:16px;display:flex;gap:8px;justify-content:center">
        <?php for ($i=1; $i<=$paginas; $i++): ?>
          <a class="gk-btn <?= $i===$pagina?'gk-btn-primary':'' ?>" href="<?= $base ?>/productos.php?p=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</section>

<footer class="footer">
  <p>© <?= date('Y') ?> Geekerath</p>
</footer>

<?php echo '</main></div>'; ?>
</body>
</html>
