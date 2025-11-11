<?php
require __DIR__ . '/api/db.php';
session_start();

// Base path (p.ej. "/ecommerce")
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }

// Cargar categorías desde BD
$cats = [];
try {
  $st = $pdo->query("SELECT id, nombre, slug FROM categorias WHERE activo = 1 ORDER BY nombre ASC");
  $rows = $st->fetchAll();
  foreach ($rows as $r) {
    $cats[] = [
      'name' => $r['nombre'],
      'slug' => $r['slug'],
      'img'  => "img/bg-{$r['slug']}.jpg", // intentamos img por slug
    ];
  }
} catch (Throwable $e) {
  $cats = [];
}

// Fallback si la tabla está vacía
if (!$cats) {
  $cats = [
    ['name' => 'Marvel',     'slug' => 'marvel',     'img' => 'img/bg-marvel.jpg'],
    ['name' => 'DC Comics',  'slug' => 'dc-comics',  'img' => 'img/bg-dc-comics.jpg'],
    ['name' => 'Star Wars',  'slug' => 'star-wars',  'img' => 'img/bg-star-wars.jpg'],
    ['name' => 'Merch',      'slug' => 'merch',      'img' => 'img/bg-merch.jpg'],
  ];
}

// Conteo de productos por categoría (opcional)
$counts = [];
try {
  $st = $pdo->query("SELECT c.slug, COUNT(*) AS n
                     FROM productos p
                     JOIN categorias c ON c.id = p.categoria_id
                     WHERE p.activo = 1
                     GROUP BY c.slug");
  foreach ($st->fetchAll() as $r) { $counts[$r['slug']] = (int)$r['n']; }
} catch (Throwable $e) {}

$title = "Categorías";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= $base ?>/styles.css">
  <style>
    .cats-wrap{max-width:1080px;margin:24px auto;padding:0 16px}
    .cats-grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(230px,1fr))}
    .cat-card{background:var(--gk-surface,#121826);border:1px solid var(--gk-border,#1f2937);
      border-radius:14px;overflow:hidden;box-shadow:var(--gk-shadow,0 10px 25px rgba(0,0,0,.25));
      text-decoration:none;color:inherit}
    .cat-card img{width:100%;height:140px;object-fit:cover;display:block}
    .cat-body{padding:12px}
    .cat-title{font-weight:700;margin:0 0 4px}
    .cat-meta{font-size:.9rem;color:#9ca3af}
  </style>
</head>
<?php include __DIR__ . '/partials/geekerath_layout.php'; ?>
<body class="theme-dark">

<section class="cats-wrap">
  <h1 class="section-title">Categorías</h1>
  <div class="cats-grid">
    <?php foreach ($cats as $c): 
      $href = $base . "/categoria.php?cat=" . urlencode($c['slug']);
      $img  = $base . '/' . ltrim($c['img'], '/');
      ?>
      <a class="cat-card" href="<?= $href ?>">
        <img src="<?= htmlspecialchars($img) ?>"
             alt="<?= htmlspecialchars($c['name']) ?>"
             onerror="this.src='<?= $base ?>/img/placeholder.jpg'">
        <div class="cat-body">
          <div class="cat-title"><?= htmlspecialchars($c['name']) ?></div>
          <div class="cat-meta"><?= $counts[$c['slug']] ?? 0 ?> productos</div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<footer class="footer"><p>© <?= date('Y') ?> Geekerath</p></footer>
<?php echo '</main></div>'; ?>
</body>
</html>

