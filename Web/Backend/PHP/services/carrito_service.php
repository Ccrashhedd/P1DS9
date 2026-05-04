<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

/**
 * Obtiene los detalles de productos en el carrito desde la base de datos
 * @param array $productosSelect Array de [idProducto, cantidad, ...]
 * @return array Array de productos con detalles de BD
 */
function getCarritoItems(array $productosSelect): array
{
    if (empty($productosSelect)) {
        return [];
    }

    try {
        $ids = array_map(fn($item) => $item['idProducto'] ?? 0, $productosSelect);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT idProducto, nombre, precioVenta FROM productos WHERE idProducto IN ({$placeholders})";
        $stmt = db()->prepare($sql);
        $stmt->execute($ids);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Obtiene las tarjetas del usuario logueado
 * @return array Array de tarjetas
 */
function getTarjetas(): array
{
    try {
        $sql = 'SELECT idTarjeta, tipo, digitos, saldo FROM tarjeta ORDER BY saldo DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Procesa el pago del carrito insertando factura y detalles
 * @param array $productosSelect Array de productos con cantidad
 * @param array $pagoData Array con idTarjeta, subtotal, itbms, total
 * @return string Mensaje de éxito o error
 */
function pagoCarrito(array $productosSelect, array $pagoData): string
{
    $pdo = db();

    try {
        $pdo->beginTransaction();

        $sql = 'INSERT INTO factura (idTarjeta, subtotal, itbms, total) VALUES (?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            (int) $pagoData['idTarjeta'],
            (float) $pagoData['subtotal'],
            (float) $pagoData['itbms'],
            (float) $pagoData['total'],
        ]);

        $facturaId = $pdo->lastInsertId();

        $sqlDetalle = 'INSERT INTO factura_detalle (idFactura, idProducto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)';
        $stmtDetalle = $pdo->prepare($sqlDetalle);

        foreach ($productosSelect as $producto) {
            $stmtDetalle->execute([
                (int) $facturaId,
                (int) $producto['idProducto'],
                (int) $producto['cantidad'],
                (float) $producto['precioUnitario'] ?? 0,
            ]);
        }

        $pdo->commit();
        return 'Pago realizado con éxito. Número de factura: ' . $facturaId;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return 'Error al procesar el pago: ' . $e->getMessage();
    }
}
