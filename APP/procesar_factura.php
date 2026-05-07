<?php
include 'db_config.php';
$data = json_decode(file_get_contents('php://input'), true);

$conn->begin_transaction();
try {
    // 1. Validar tarjeta y saldo
    $stmt = $conn->prepare("SELECT idTarjeta, saldo FROM tarjeta WHERE digitos = ?");
    $stmt->bind_param("s", $data['digitosTarjeta']);
    $stmt->execute();
    $tarjeta = $stmt->get_result()->fetch_assoc();

    if (!$tarjeta || $tarjeta['saldo'] < $data['total']) {
        throw new Exception("Tarjeta inválida o saldo insuficiente");
    }

    // 2. Descontar saldo
    $nuevoSaldo = $tarjeta['saldo'] - $data['total'];
    $conn->query("UPDATE tarjeta SET saldo = $nuevoSaldo WHERE idTarjeta = " . $tarjeta['idTarjeta']);

    // 3. Crear Factura
    $stmt = $conn->prepare("INSERT INTO factura (idTarjeta, subtotal, itbms, total) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iddd", $tarjeta['idTarjeta'], $data['subtotal'], $data['itbms'], $data['total']);
    $stmt->execute();
    $idFactura = $conn->insert_id;

    // 4. Detalle y Stock
    foreach ($data['items'] as $item) {
        $stmt = $conn->prepare("INSERT INTO factura_detalle (idFactura, idProducto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $idFactura, $item['idProducto'], $item['cantidad'], $item['precioUnitario']);
        $stmt->execute();
        
        $conn->query("UPDATE productos SET stock = stock - " . $item['cantidad'] . " WHERE idProducto = " . $item['idProducto']);
    }

    $conn->commit();
    echo json_encode(["ok" => true, "mensaje" => "Compra exitosa"]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["ok" => false, "mensaje" => $e->getMessage()]);
}
?>