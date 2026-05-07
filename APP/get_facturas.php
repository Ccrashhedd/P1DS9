<?php
include 'db_config.php';
header('Content-Type: application/json');

// Consulta para obtener las facturas y sus detalles (incluyendo el nombre del producto)
$sql = "SELECT f.*, fd.idProducto, fd.cantidad, fd.precio_unitario, p.nombre as nombreProducto 
        FROM factura f
        LEFT JOIN factura_detalle fd ON f.idFactura = fd.idFactura
        LEFT JOIN productos p ON fd.idProducto = p.idProducto
        ORDER BY f.idFactura DESC";

$result = $conn->query($sql);

$facturas = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $idFac = $row['idFactura'];
        
        // Si la factura no ha sido agregada al array, la creamos con sus datos base
        if (!isset($facturas[$idFac])) {
            $facturas[$idFac] = array(
                "idFactura" => (int)$idFac,
                "idTarjeta" => (int)$row['idTarjeta'],
                "subtotal" => (double)$row['subtotal'],
                "itbms" => (double)$row['itbms'],
                "total" => (double)$row['total'],
                "fecha" => "2024-05-06", // Aquí podrías usar una columna fecha si la tienes
                "detalles" => array()
            );
        }
        
        // Agregamos el producto al detalle de esta factura
        if ($row['idProducto'] != null) {
            $facturas[$idFac]['detalles'][] = array(
                "idProducto" => $row['idProducto'],
                "nombreProducto" => $row['nombreProducto'],
                "cantidad" => (int)$row['cantidad'],
                "precio_unitario" => (double)$row['precio_unitario']
            );
        }
    }
    
    // Reindexar el array para que sea una lista JSON válida
    echo json_encode(array_values($facturas));
} else {
    echo json_encode([]);
}
?>