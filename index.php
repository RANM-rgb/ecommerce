<?php include __DIR__.'/landing.php'; ?>
<?php
require __DIR__ . "/api/db.php"; // conexión PDO a tu RDS
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Geekerath Store</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #111;
      color: #fff;
      margin: 0;
      padding: 0;
    }
    header {
      background: #202020;
      padding: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    header h1 {
      margin: 0;
      font-size: 1.5rem;
      color: #ffcc00;
    }
    main {
      padding: 30px;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
    }
    .card {
      background: #1c1c1c;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.4);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .card img {
      width: 100%;
      height: 240px;
      object-fit: cover;
    }
    .card-content {
      padding: 15px;
    }
    .card-content h3 {
      margin: 0 0 10px;
      font-size: 1.1rem;
      color: #ffcc00;
    }
    .card-content p {
      font-size: 0.9rem;
      color: #ccc;
      margin: 0 0 10px;
    }
    .price {
      font-weight: bold;
      color: #00ff99;
      font-size: 1rem;
    }
    .add-btn {
      background: #ffcc00;
      border: none;
      color: #111;
      padding: 10px;
      width: 100%;
      cursor: pointer;
      font-weight: bold;
    }
    .add-btn:hover {
      background: #ffe066;
    }
  </style>
</head>

<body>
  <header>
    <h1>🛒 Geekerath Store</h1>
    <a href="checkout.php" style="color:white;text-decoration:none;font-weight:bold;">Ver Carrito</a>
  </header>

  <main>
    <h2>Catálogo de Productos</h2>

    <div class="grid">
      <?php
      try {
        $stmt = $pdo->query("SELECT * FROM productos ORDER BY id_producto DESC");
        $productos = $stmt->fetchAll();

        if (!$productos) {
          echo "<p>No hay productos registrados aún.</p>";
        } else {
          foreach ($productos as $p) {
            $imagen = htmlspecialchars($p['imagen']);
            // Si tu campo "imagen" solo guarda el nombre, apunta a /img/
            if (!preg_match('/^https?:/i', $imagen)) {
              $imagen = "img/" . $imagen;
            }
            echo "
              <div class='card'>
                <img src='{$imagen}' alt='".htmlspecialchars($p['nombre'])."'>
                <div class='card-content'>
                  <h3>".htmlspecialchars($p['nombre'])."</h3>
                  <p>".htmlspecialchars($p['descripcion'])."</p>
                  <div class='price'>$".number_format($p['precio'], 2)."</div>
                </div>
                <form action='add_to_cart.php' method='POST'>
                  <input type='hidden' name='id_producto' value='{$p['id_producto']}'>
                  <button class='add-btn' type='submit'>Agregar al carrito</button>
                </form>
              </div>
            ";
          }
        }
      } catch (PDOException $e) {
        echo "<p>Error al obtener productos: " . htmlspecialchars($e->getMessage()) . "</p>";
      }
      ?>
    </div>
  </main>
</body>
</html>
