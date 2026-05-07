<?php
// Ocultar warnings de PHP para que no rompan el formato JSON de Retrofit
error_reporting(0);
header('Content-Type: application/json');
include 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validar que el JSON llegó correctamente desde Android
if (!$data || !isset($data['digitosTarjeta']) || !isset($data['total'])) {
    echo json_encode(["ok" => false, "mensaje" => "Faltan datos para procesar el pago"]);
    exit;
}

$conn->begin_transaction();

try {
    // Usamos trim() para borrar espacios invisibles al inicio o final de los dígitos
// str_replace busca cualquier espacio " " y lo elimina dejándolo vacío ""
$digitos = str_replace(" ", "", $data['digitosTarjeta']);    $total = (float) $data['total'];
    $subtotal = (float) $data['subtotal'];
    $itbms = (float) $data['itbms'];

    // 1. Validar tarjeta (Buscamos SOLO por dígitos, tal como en tu rollback exitoso)
    $stmt = $conn->prepare("SELECT idTarjeta, saldo FROM tarjeta WHERE digitos = ?");
    $stmt->bind_param("s", $digitos);
    $stmt->execute();
    $tarjeta = $stmt->get_result()->fetch_assoc();

    // 1.1 Validar si la tarjeta existe
    if (!$tarjeta) {
        throw new Exception("La tarjeta terminada en " . substr($digitos, -4) . " no existe.");
    }

    // 1.2 Validar si el saldo es suficiente
    if ($tarjeta['saldo'] < $total) {
        throw new Exception("Saldo insuficiente. Tienes $" . number_format($tarjeta['saldo'], 2) . " y necesitas $" . number_format($total, 2));
    }

    // 2. Descontar saldo (Usando prepare por seguridad)
    $nuevoSaldo = $tarjeta['saldo'] - $total;
    $updateTarjeta = $conn->prepare("UPDATE tarjeta SET saldo = ? WHERE idTarjeta = ?");
    $updateTarjeta->bind_param("di", $nuevoSaldo, $tarjeta['idTarjeta']);
    $updateTarjeta->execute();

    // 3. Crear Factura
    $stmtFactura = $conn->prepare("INSERT INTO factura (idTarjeta, subtotal, itbms, total) VALUES (?, ?, ?, ?)");
    $stmtFactura->bind_param("iddd", $tarjeta['idTarjeta'], $subtotal, $itbms, $total);
    $stmtFactura->execute();
    $idFactura = $conn->insert_id;

    // 4. Detalle y descontar Stock
    $stmtDetalle = $conn->prepare("INSERT INTO factura_detalle (idFactura, idProducto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    $stmtStock = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE idProducto = ?");

    foreach ($data['items'] as $item) {
        $idProd = $item['idProducto'];
        $cant = $item['cantidad'];
        $precio = $item['precioUnitario'];

        // Guardar el detalle de la factura
        $stmtDetalle->bind_param("iiid", $idFactura, $idProd, $cant, $precio);
        $stmtDetalle->execute();
        
        // Descontar el stock del producto
        $stmtStock->bind_param("ii", $cant, $idProd);
        $stmtStock->execute();
    }

    // ✅ Todo perfecto, guardamos los cambios
    $conn->commit();
    echo json_encode(["ok" => true, "mensaje" => "Compra procesada con éxito"]);

} catch (Exception $e) {
    // ❌ Error (Sin saldo o no existe), se revierte todo
    $conn->rollback();
    echo json_encode(["ok" => false, "mensaje" => $e->getMessage()]);
}
?>