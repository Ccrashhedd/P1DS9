<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';



function obtenerEmpleados() : array {
    try {
        $pdo = db();
        $stmt = $pdo->query('SELECT usuario, nombre, apellido, rol FROM empleado WHERE usuario NOT IN ("admin","empleado") ORDER BY nombre ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function obtenerEmpleado(string $usuario): ?array {

    try {

        $pdo = db();

        $stmt = $pdo->prepare('SELECT * FROM empleado WHERE usuario = :usuario');

        $stmt->execute([
            ':usuario' => $usuario
        ]);

        $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

        return $empleado ?: null;

    } catch (PDOException $e) {

        return null;
    }
}


// para agregar y editar empleados

function agregarEmpleado(array $data) : string {
    // Validar variables

    $nombre = $data['nombre'] ?? '';
    $apellido = $data['apellido'] ?? '';
    $usuario = $data['usuario'] ?? '';
    $contrasena = $data['contrasena'] ?? '';
    $rol = $data['rol'] ?? '';
    if (empty($usuario) || empty($contrasena) || empty($rol)) {
        return 'Los campos usuario, contraseña y rol son obligatorios.';
    }
    
    try {
        $pdo = db();
        $stmt = $pdo->prepare('INSERT INTO empleado (nombre, apellido, usuario, contrasena, rol) VALUES (:nombre, :apellido, :usuario, :contrasena, :rol)');
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':usuario' => $usuario,
            ':contrasena' => $contrasena,
            ':rol' => $rol
        ]);
        return 'Empleado agregado exitosamente.';
    } catch (PDOException $e) {
        return 'Error al agregar el empleado: ' . $e->getMessage();
    }
}


?>
