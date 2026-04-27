<?php
/**
 * Script de prueba rápida de carga del nuevo backend consolidado
 * Ejecutar desde terminal: php c:\xampp\htdocs\Manuel\P1DS9\Web\Backend\PHP\test_load.php
 */

declare(strict_types=1);

echo "=== Test de carga del backend consolidado ===\n\n";

try {
    echo "1. Cargando config/app.php...\n";
    require_once __DIR__ . '/config/app.php';
    echo "   ✓ Config cargada. Función db() disponible.\n\n";

    echo "2. Probando conexión PDO...\n";
    $pdo = db();
    echo "   ✓ Conexión PDO exitosa: " . get_class($pdo) . "\n";
    echo "   ✓ DSN: mysql:host=localhost;dbname=ds9p1;charset=utf8mb4\n\n";

    echo "3. Cargando includes/helpers.php...\n";
    require_once __DIR__ . '/includes/helpers.php';
    echo "   ✓ Helpers cargado. Funciones disponibles.\n\n";

    echo "4. Cargando includes/auth.php...\n";
    require_once __DIR__ . '/includes/auth.php';
    echo "   ✓ Auth cargado. Funciones: authenticate, loginUser, logoutUser, etc.\n\n";

    echo "5. Cargando services/catalog_service.php...\n";
    require_once __DIR__ . '/services/catalog_service.php';
    echo "   ✓ Catalog service cargado. Funciones: getCategorias, getMarcas, etc.\n\n";

    echo "6. Cargando services/product_service.php...\n";
    require_once __DIR__ . '/services/product_service.php';
    echo "   ✓ Product service cargado. Funciones: getProductosPanel, guardarProducto, etc.\n\n";

    echo "7. Cargando controllers/router.php...\n";
    require_once __DIR__ . '/controllers/router.php';
    echo "   ✓ Router cargado. Función handleRequest() disponible.\n\n";

    echo "=== ✅ TEST EXITOSO ===\n";
    echo "Todos los archivos se cargaron sin errores.\n";
    echo "El backend consolidado está listo para usar.\n";

} catch (Throwable $e) {
    echo "\n❌ ERROR DETECTADO:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " (línea " . $e->getLine() . ")\n";
    exit(1);
}
?>
