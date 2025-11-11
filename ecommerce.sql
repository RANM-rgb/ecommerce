CREATE DATABASE ecommerce;
USE ecommerce;
CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  direccion TEXT
);
CREATE TABLE productos (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100),
  descripcion TEXT,
  precio DECIMAL(10,2),
  imagen VARCHAR(255)
);
CREATE TABLE carrito (
  id_carrito INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  id_producto INT,
  cantidad INT DEFAULT 1,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);
CREATE TABLE pedidos (
  id_pedido INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT,
  fecha_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(10,2),
  estado ENUM('pendiente','pagado','cancelado') DEFAULT 'pendiente',
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);
CREATE TABLE detalle_pedido (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT,
  id_producto INT,
  cantidad INT,
  subtotal DECIMAL(10,2),
  FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
  FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);
CREATE TABLE pagos (
  id_pago INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT,
  metodo ENUM('tarjeta','paypal','transferencia'),
  monto DECIMAL(10,2),
  fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('exitoso','fallido') DEFAULT 'exitoso',
  FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido)
);
INSERT INTO usuarios (nombre, email, password, direccion) VALUES
('Carlos Hernández', 'carlos@example.com', '123456', 'Av. Hidalgo 101, Pachuca, Hidalgo'),
('Ana López', 'ana@example.com', 'abcdef', 'Calle Juárez 45, CDMX'),
('Luis García', 'luis@example.com', 'qwerty', 'Blvd. Las Torres 200, Puebla');
INSERT INTO productos (nombre, descripcion, precio, imagen) VALUES
-- 🦇 DC Comics
('Batman Bust', 'Busto coleccionable de edición limitada de Batman.', 2200.00, 'batman_bust.jpg'),
('Superman Classic', 'Estatua premium de Superman volando.', 2800.00, 'superman_classic.jpg'),
('Estatua de Batman', 'Estatua coleccionable con capa dinámica.', 2100.00, 'batman_estatua.jpg'),
('Funko Superman', 'Figura Funko Pop de Superman clásico.', 350.00, 'funko_superman.jpg'),
('Funko Pop Krypto', 'Figura Funko Pop del personaje Krypto.', 300.00, 'funko_krypto.jpg'),
('Lego Justice League', 'Aquaman Batalla en la Atlántida (76085).', 600.00, 'lego_justice_league.jpg'),

-- ⭐ Star Wars
('LEGO Star Wars X-Wing', 'Set LEGO de la nave X-Wing con minifiguras.', 1250.00, 'lego_xwing.jpg'),
('Star Wars The Black Series The Mandalorian', 'Figura de acción coleccionable de 15 cm.', 550.00, 'black_series_mandalorian.jpg'),
('Funko Pop Ahsoka', 'Figura Funko Pop del personaje Ahsoka.', 450.00, 'funko_ahsoka.jpg'),

-- 🧥 Merch (Geekerrath)
('Sudadera', 'Sudadera conmemorativa de la tienda Geekerrath.', 700.00, 'sudadera_geekerrath.jpg'),
('Playera', 'Playera conmemorativa de la tienda Geekerrath.', 600.00, 'playera_geekerrath.jpg'),
('Taza', 'Taza con el logotipo de Geekerrath.', 600.00, 'taza_geekerrath.jpg'),

-- 🦸 Marvel
('MARVEL Spider-Man VenomVersus Epic World of Action', 'Figura detallada Mafex con múltiples accesorios.', 400.00, 'spiderman_venomversus.jpg'),
('Iron Man Marvel Legends', 'Figura articulada de Iron Man con efectos.', 600.00, 'ironman_legend.jpg'),
('Funko Pop The Thing', 'Figura Funko Pop del personaje The Thing (Los 4 Fantásticos).', 1200.00, 'funko_thething.jpg'),
('LEGO Marvel X-Jet de los X-Men',
 'Kit de modelo de avión de juguete construible 76281.',
 2800.00,
 'C:/Geekerath/Lego X men.jpg');



SELECT * FROM productos
WHERE nombre LIKE '%Lego%' OR nombre LIKE '%X-Men%';

