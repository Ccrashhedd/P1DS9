Estructura reorganizada

Frontend
- Frontend/HTML/index.php -> punto único de entrada
- Frontend/HTML/views/ -> vistas de catálogo, login, dashboard y panel
- Frontend/HTML/partials/ -> encabezado compartido
- Frontend/Assets/CSS/app.css -> estilos

Backend
- Backend/PHP/config/ -> conexión y configuración
- Backend/PHP/includes/ -> helpers y autenticación
- Backend/PHP/controllers/router.php -> controlador principal
- Backend/PHP/services/ -> consultas del catálogo y productos

Importante
- La navegación principal ahora siempre pasa por Frontend/HTML/index.php.
- Aunque cambies entre catálogo, login, dashboard o productos, el navegador sigue en la misma ruta base de index.php.
- Los archivos login.php, dashboard.php, productos_panel.php y logout.php quedaron solo como redirecciones de compatibilidad.

Prueba rápida
1. Importa la base ds9p1.sql en MySQL.
2. Abre la ruta:
   localhost/DS92026/P1DS9/Web/PaginaCliente/Frontend/HTML/index.php
3. Usuario admin: admin / admin123
4. Usuario empleado: empleado / empleado123

Notas
- El campo del código del producto está listo para lector de código de barras tipo USB.
- Las imágenes del catálogo deben colocarse en una carpeta accesible desde:
  Web/PaginaCliente/Frontend/HTML/../img/productos/
  Si no existen, se mostrará una imagen de reemplazo.
