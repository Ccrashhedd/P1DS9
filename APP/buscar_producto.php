<?php
include 'db_config.php';

// Verificamos si se envió el parámetro idProducto (que funciona como código de barras en el escáner)
if (!isset($_GET['idProducto'])) {
    echo json_encode(["ok" => false, "mensaje" => "Falta el código del producto"]);
    exit;
}

$idProducto = $_GET['idProducto'];

$stmt = $conn->prepare("SELECT * FROM productos WHERE idProducto = ?");
$stmt->bind_param("s", $idProducto); // Usamos 's' por si el código de barras es muy largo
$stmt->execute();
$res = $stmt->get_result();

if ($producto = $res->fetch_assoc()) {
    echo json_encode(["ok" => true, "mensaje" => "Producto encontrado", "datos" => $producto]);
} else {
    // Retornamos ok=false para que Android sepa que es un producto nuevo y abra el formulario vacío
    echo json_encode(["ok" => false, "mensaje" => "Producto no registrado"]);
}
?>