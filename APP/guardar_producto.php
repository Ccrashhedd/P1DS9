<?php
include 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['idProducto'])) {
    echo json_encode(["ok" => false, "mensaje" => "Datos incompletos"]);
    exit;
}

$idProducto = $data['idProducto'];
$nombre = $data['nombre'];
$unidad = $data['unidad'] ?? 'Unidad';
$descripcion = $data['descripcion'] ?? '';
$stock = $data['stock'];
$precioCosto = $data['precioCosto'];
$precioVenta = $data['precioVenta'];
$idCategoria = $data['idCategoria'] ?? 1;
$idMarca = $data['idMarca'] ?? 1;

// 1. Verificamos si el producto ya existe
$stmt = $conn->prepare("SELECT idProducto FROM productos WHERE idProducto = ?");
$stmt->bind_param("s", $idProducto);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // EXISTE: Actualizar stock y datos
    $update = $conn->prepare("UPDATE productos SET nombre=?, unidad=?, descripcion=?, stock=?, precioCosto=?, precioVenta=?, idCategoria=?, idMarca=? WHERE idProducto=?");
    $update->bind_param("sssidddis", $nombre, $unidad, $descripcion, $stock, $precioCosto, $precioVenta, $idCategoria, $idMarca, $idProducto);
    
    if ($update->execute()) {
        echo json_encode(["ok" => true, "mensaje" => "Producto actualizado con éxito"]);
    } else {
        echo json_encode(["ok" => false, "mensaje" => "Error al actualizar producto"]);
    }
} else {
    // NO EXISTE: Insertar nuevo producto
    $insert = $conn->prepare("INSERT INTO productos (idProducto, nombre, unidad, descripcion, stock, precioCosto, precioVenta, idCategoria, idMarca) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssidddi", $idProducto, $nombre, $unidad, $descripcion, $stock, $precioCosto, $precioVenta, $idCategoria, $idMarca);
    
    if ($insert->execute()) {
        echo json_encode(["ok" => true, "mensaje" => "Nuevo producto registrado con éxito"]);
    } else {
        echo json_encode(["ok" => false, "mensaje" => "Error al registrar producto"]);
    }
}
?>