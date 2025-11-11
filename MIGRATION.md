
# Geekerath Migration Snapshot

- Base project: ecommerce.zip
- Theme assets imported from: Geekerath.zip into `/geekerath_assets/`.
- Preserved files: ['api/db.php', 'productos.php']
- Header updated to load Geekerath assets (non-breaking).
- Pages wrapped with `partials/geekerath_layout.php`: index.php, landing.php, categoria.php, checkout.php, productos.php
- Guard added to productos.php to ensure DB include.
