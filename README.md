# 🛍️ Geekerath E-Commerce

**Geekerath** es una tienda en línea de temática geek desarrollada en **PHP (nativo)** con base de datos **MySQL**, que implementa un flujo completo de compra: catálogo, carrito, checkout, autenticación y área de usuario con historial de pedidos.

---

## 🚀 Características principales

### 🧱 Frontend
- Interfaz moderna y responsive (modo oscuro) con HTML5, CSS3 y algo de JS vanilla.  
- Carrusel de videos e imágenes en la landing (`landing.php` / `index.php`).  
- Catálogo dinámico de productos con búsqueda, categorías y tarjetas con stock.  
- Carrito interactivo con actualización de cantidades y subtotal en tiempo real.  
- Sección “Los más vendidos” y videos de unboxing en portada.

### 🔐 Autenticación
- Registro de usuario con verificación de cuenta por token (`register.php`).  
- Inicio de sesión seguro con `password_hash` y `password_verify`.  
- Control de sesión persistente y middleware `require_login()` para proteger rutas.  
- Registro de eventos (`auth_events`) para auditoría (login, logout, verify, etc).

### 💳 Checkout y pagos simulados
- Flujo de compra con carrito → checkout → pago → confirmación.  
- Simulación de métodos de pago (`paypal_sim`, `card_sim`, `oxxo_sim`).  
- Inserción automática en tablas:
  - `pedidos`  
  - `pedido_items`  
  - `pagos_simulados`  
  - `pago_eventos`  
- Transacciones SQL con rollback seguro.

### 👤 Área de usuario
- Página **“Mis pedidos”** con:
  - Estado del pedido (`pendiente`, `aprobado`, `fallido`).  
  - Estado del pago (`created`, `approved`, etc).  
  - Detalle de ítems comprados (cantidad, precio, subtotal).  
- Página **“Perfil”** con datos del usuario y acceso rápido a historial.

---

## 🧩 Estructura del proyecto

