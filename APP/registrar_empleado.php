<?php
// MUY IMPORTANTE: Que no haya espacios en blanco ni líneas vacías antes de <?php
header('Content-Type: application/json');
include 'db_config.php'; // Cambiado a tu archivo real

// Leer los datos enviados desde Android
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['usuario'])) {
    $usuario = $data['usuario'];
    $nombre = $data['nombre'];
    $apellido = $data['apellido'];
    $pass = $data['contrasena'];
    $rol = 2; // Empleado fijo

    // INSERTAR CON SENTENCIAS PREPARADAS (Seguridad y consistencia)
    $stmt = $conn->prepare("INSERT INTO empleado (usuario, nombre, apellido, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $usuario, $nombre, $apellido, $pass, $rol);

    if ($stmt->execute()) {
        echo json_encode(["ok" => true, "mensaje" => "Empleado registrado exitosamente"]);
    } else {
        // Si hay error en la base de datos (ej. usuario duplicado)
        echo json_encode(["ok" => false, "mensaje" => "Error al registrar: " . $conn->error]);
    }
} else {
    echo json_encode(["ok" => false, "mensaje" => "No se recibieron datos validos"]);
}
?>