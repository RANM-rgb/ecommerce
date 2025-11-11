<?php
require __DIR__ . '/api/db.php';
session_start();

/**
 * Mapa de categorías visibles -> Nombre y slug en DB
 * Ajusta los slugs si en tu tabla `categorias` usas otros.
 */
$cats = [
  'marvel'     => ['name' => 'Marvel',     'slug' => 'marvel'],
  'dc'         => ['name' => 'DC Comics',  'slug' => 'dc-comics'],
  'dc-comics'  => ['name' => 'DC Comics',  'slug' => 'dc-comics'],
  'starwars'   => ['name' => 'Star Wars',  'slug' => 'star-wars'],
  'star-wars'  => ['name' => 'Star Wars',  'slug' => 'star-wars'],
  'merch'      => ['name' => 'Merch',      'slug' => 'merch'],
];

$catKey  = isset($_GET['cat']) ? strtolower($_GET['cat']) : 'dc-comics';
$config  = $cats[$catKey] ?? $cats['dc-comics'];
$catName = $config['name'];
$catSlug = $config['slug'];

$title = "Catálogo $catName";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="styles.css">
  <style>
    .category-hero{
      background: url('img/bg-<?= htmlspecialchars($catKey) ?>.jpg') center/cover no-repeat fixed, #0c1f4f;
      padding: 32px 16px; border-bottom: 3px solid #f6c700;
    }
    .category-hero .box{
      max-width: 1080px; margin: 0 auto;
      background: rgba(255,255,255,.92);
      border: 2px solid #f6c700; border-radius: 10px;
      padding: 18px;
    }
  </style>
</head>
<?php include __DIR__ . '/partials/geekerath_layout.php'; ?>
<body class="theme-dark">
<?php // OJO: geekerath_layout.php ya incluye header.php internamente. ?>

<section class="category-hero">
  <div class="box">
    <h1 class="section-title">Catálogo <?= htmlspecialchars($catName) ?></h1>
    <p class="muted">Explora nuestros productos de <?= htmlspecialchars($catName) ?>.</p>
  </div>
</section>

<section class="container">
  <div class="grid">
    <?php
    // 1) Intento por SLUG
    $sql = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, p.stock
            FROM productos p
            JOIN categorias c ON c.id = p.categoria_id
            WHERE c.slug = :slug AND p.activo = 1
            ORDER BY p.created_at DESC, p.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':slug' => $catSlug]);
    $rows = $stmt->fetchAll();

    // 2) Fallback: si no encontró por slug, busca por NOMBRE de categoría
    if (!$rows) {
      $sql2 = "SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, p.stock
               FROM productos p
               JOIN categorias c ON c.id = p.categoria_id
               WHERE c.nombre = :name AND p.activo = 1
               ORDER BY p.created_at DESC, p.id DESC";
      $stmt2 = $pdo->prepare($sql2);
      $stmt2->execute([':name' => $catName]);
      $rows = $stmt2->fetchAll();
    }

    foreach ($rows as $p):
      $img = $p['imagen'];
      if (!preg_match('/^https?:/i', $img) && stripos($img, 'img/') !== 0) {
        $img = 'img/' . ltrim($img, '/');
      }
    ?>
      <article class="card card--compact">
        <div class="card__media">
          <img src="<?= htmlspecialchars($img) ?>"
               alt="<?= htmlspecialchars($p['nombre']) ?>"
               onerror="this.src='img/placeholder.jpg'">
        </div>
        <div class="card__body">
          <h3 class="card__title"><?= htmlspecialchars($p['nombre']) ?></h3>
          <p class="card__desc"><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>
          <div class="card__meta">
            <span class="price">$<?= number_format((float)$p['precio'], 2) ?></span>
            <span class="stock <?= ($p['stock'] ?? 0) > 0 ? 'ok' : 'out' ?>">
              <?= ($p['stock'] ?? 0) > 0 ? ('Disponibles: ' . (int)$p['stock']) : 'Agotado' ?>
            </span>
          </div>
        </div>

        <!-- Formulario: regresar a la MISMA página tras agregar -->
        <form class="card__action" action="add_to_cart.php" method="post">
          <input type="hidden" name="id_producto" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
          <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
          <button class="btn btn-accent" <?= ($p['stock'] ?? 0) > 0 ? '' : 'disabled' ?>>
            Agregar al carrito
          </button>
        </form>
      </article>
    <?php endforeach; ?>

    <?php if (!$rows): ?>
      <div class="alert">
        No encontramos productos para esta categoría. ¿Quizá aún no has asignado la categoría
        <strong><?= htmlspecialchars($catName) ?></strong> a tus productos?
      </div>
    <?php endif; ?>
  </div>
</section>

<footer class="footer">
  <p>© <?= date('Y') ?> Geekerath</p>
</footer>

<?php echo '</main></div>'; // close layout if needed ?>
</body>
</html>
