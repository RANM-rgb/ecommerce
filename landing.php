<?php
require __DIR__.'/api/db.php';
session_start();
$title = 'Geekerath Store';

/* Base del proyecto, p.ej. "/ecommerce" en localhost */
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($base === '/' || $base === '\\') { $base = ''; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= $base ?>/styles.css">

  <style>
    .hero__video{width:100%;max-height:420px;object-fit:cover;display:block;border-radius:12px}
    .mini-carousel{position:relative}
    .mini-carousel__track{display:flex;transition:transform .3s ease;overflow:hidden}
    .mini-carousel__item{min-width:100%}
    .mini-carousel__item img,
    .mini-carousel__item video{width:100%;height:360px;object-fit:cover;display:block;border-radius:12px}
    .mini-carousel__btn{position:absolute;top:50%;transform:translateY(-50%);z-index:2}
    .mini-carousel__btn.prev{left:.5rem}
    .mini-carousel__btn.next{right:.5rem}
  </style>
</head>
<?php include __DIR__.'/partials/geekerath_layout.php'; ?>
<body class="theme-dark">



<section class="hero">
  <div class="hero__center">
    <video class="hero__video" autoplay muted loop playsinline poster="<?= $base ?>/img/Ofertas.png">
      <source src="<?= $base ?>/img/fundador.mp4" type="video/mp4">
    </video>
    <h1 class="hero__title">¡Empieza tu recorrido!</h1>
    <p class="hero__subtitle">Ofertas de hasta 50% de descuento</p>
   <a class="btn btn-primary" href="<?= $base ?>/productos.php">¡Comprar ahora!</a>

  </div>
</section>

<section class="strip">
  <div class="strip__inner">
    <h2 class="section-title">Revisa nuestros unboxings e imágenes de productos</h2>

    <div class="mini-carousel" data-carousel>
      <button class="mini-carousel__btn prev" data-prev>‹</button>

      <div class="mini-carousel__track" data-track>
        <div class="mini-carousel__item">
          <video controls muted preload="metadata" poster="<?= $base ?>/img/lego_xwing.jpg">
            <source src="<?= $base ?>/img/funko_pop_Superman.mp4" type="video/mp4">
          </video>
        </div>

        <div class="mini-carousel__item">
          <img src="<?= $base ?>/img/MARVEL_Spiderman.jpg" alt="Spidey"
               onerror="this.src='<?= $base ?>/img/placeholder.jpg'">
        </div>

        <div class="mini-carousel__item">
          <img src="<?= $base ?>/img/sudadera.jpg" alt="Sudadera"
               onerror="this.src='<?= $base ?>/img/placeholder.jpg'">
        </div>
      </div>

      <button class="mini-carousel__btn next" data-next>›</button>
    </div>
  </div>
</section>

<section class="container">
  <h2 class="section-title">Los Más Vendidos</h2>
  <div class="grid">
    <?php
    // 👇 Cambiado: id_producto -> id
    $stmt = $pdo->query("SELECT id, nombre, descripcion, precio, imagen, stock
                         FROM productos
                         ORDER BY ventas DESC, id DESC
                         LIMIT 6");
    foreach ($stmt as $p):
      $img = $p['imagen'];
      if (!preg_match('/^https?:/i',$img) && stripos($img,'img/')!==0) {
        $img = 'img/'.$img;
      }
      $img = $base.'/'.ltrim($img,'/');
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
            <span class="price">$<?= number_format($p['precio'],2) ?></span>
            <span class="stock <?= $p['stock']>0?'ok':'out' ?>">
              <?= $p['stock']>0?('Disp: '.$p['stock']):'Agotado' ?>
            </span>
          </div>
        </div>
        <form class="card__action" action="<?= $base ?>/add_to_cart.php" method="post">
          <!-- 👇 Cambiado: id_producto -> id -->
          <input type="hidden" name="id_producto" value="<?= (int)$p['id'] ?>">
          <button class="btn btn-accent" <?= $p['stock']>0?'':'disabled' ?>>Agregar al carrito</button>
        </form>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<footer class="footer" id="contacto">
  <p>© <?= date('Y') ?> Geekerath — Todos los derechos reservados.</p>
</footer>

<script>
(function(){
  const root = document.querySelector('[data-carousel]');
  if(!root) return;
  const track = root.querySelector('[data-track]');
  const prev  = root.querySelector('[data-prev]');
  const next  = root.querySelector('[data-next]');
  let index = 0;
  const items = track.children;
  function sync(){ track.style.transform = `translateX(${-index*100}%)`; }
  next.addEventListener('click',()=>{ index=(index+1)%items.length; sync(); });
  prev.addEventListener('click',()=>{ index=(index-1+items.length)%items.length; sync(); });
})();
</script>

</body>
</html>


