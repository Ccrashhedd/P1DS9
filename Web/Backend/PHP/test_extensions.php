<?php
/**
 * Script de diagnóstico de extensiones PHP
 */

echo "=== Diagnóstico de Extensiones PHP ===\n\n";

$extensiones = [
    'pdo' => 'PDO (Base)',
    'pdo_mysql' => 'PDO MySQL Driver (REQUERIDO)',
    'mysqli' => 'MySQLi',
];

foreach ($extensiones as $ext => $nombre) {
    $disponible = extension_loaded($ext);
    $status = $disponible ? '✓' : '✗';
    $requerido = strpos($nombre, 'REQUERIDO') !== false ? ' [REQUERIDO]' : '';
    echo "{$status} {$nombre}{$requerido}\n";
}

echo "\n=== Información de PHP ===\n";
echo "Versión: " . phpversion() . "\n";
echo "XAMPP PHP Directory: " . getenv('PHPRC') ?: 'No definido' . "\n";
echo "php.ini location: " . php_ini_loaded_file() . "\n";

echo "\n=== Configuración de BD ===\n";
echo "DB_HOST: localhost\n";
echo "DB_NAME: ds9p1\n";
echo "DB_USER: root\n";

echo "\n=== Instrucciones ===\n";
if (!extension_loaded('pdo_mysql')) {
    echo "❌ PDO MySQL Driver no está habilitado.\n";
    echo "\nPara habilitar en XAMPP:\n";
    echo "1. Abrir: C:\\xampp\\php\\php.ini\n";
    echo "2. Buscar: ;extension=pdo_mysql\n";
    echo "3. Cambiar a: extension=pdo_mysql\n";
    echo "4. Guardar y reiniciar XAMPP Apache\n";
} else {
    echo "✓ PDO MySQL Driver está disponible.\n";
}
?>
