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
        $sql = 'SELECT idTarjeta, tipo, digitos, saldo, saldoMaximo FROM tarjeta ORDER BY saldo DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getCantidadStock(array $productosSelect): array
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
        $sql = "SELECT idProducto, stock FROM productos WHERE idProducto IN ({$placeholders})";
        $stmt = db()->prepare($sql);
        $stmt->execute($ids);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['idProducto']] = (int) $row['stock'];
        }
        return $result;
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
    //Validar si el carrito no esta vacio

    if (empty($productosSelect)) {
        return 'El carrito esta vacio.';
    }

    $idTarjeta = (int) ($pagoData['idTarjeta'] ?? 0);
    $subtotal = (float) ($pagoData['subtotal'] ?? 0);
    $itbms = (float) ($pagoData['itbms'] ?? 0);
    $total = (float) ($pagoData['total'] ?? 0);

    if ($idTarjeta <= 0) {
        return 'Debe seleccionar una tarjeta válida para realizar el pago.';
    }

    // Validar que la cantidad no sobrepase la cantidad en stock
    $cantidadStock = getCantidadStock($productosSelect);
    $cantidadProducto = array_column($productosSelect, 'cantidad', 'idProducto');
    /** @var array<int, int> $cantidadStock */
    foreach ($cantidadStock as $idProducto => $stock) {
        if (($cantidadProducto[$idProducto] ?? 0) > $stock) {
            return "La cantidad del producto ID {$idProducto} excede el stock disponible.";
        }
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();

        $stmtSaldo = $pdo->prepare('SELECT saldo FROM tarjeta WHERE idTarjeta = ? FOR UPDATE');
        $stmtSaldo->execute([$idTarjeta]);
        $saldoActual = $stmtSaldo->fetchColumn();

        if ($saldoActual === false) {
            $pdo->rollBack();
            return 'La tarjeta seleccionada no existe.';
        }

        if ((float) $saldoActual < $total) {
            $pdo->rollBack();
            return 'La tarjeta no tiene saldo suficiente para completar el pago.';
        }

        $sql = 'INSERT INTO factura (idTarjeta, subtotal, itbms, total) VALUES (?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idTarjeta,
            $subtotal,
            $itbms,
            $total,
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

        actualizarSaldoTarjeta($idTarjeta, $total);
        actualizarProductoStock($productosSelect);

        $pdo->commit();
        return 'Pago realizado con éxito. Número de factura: ' . $facturaId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return 'Error al procesar el pago: ' . $e->getMessage();
    }
}

function actualizarProductoStock(array $productosSelect): void
{
    if (empty($productosSelect)) {
        return;
    }

    $pdo = db();
    $sql = 'UPDATE productos SET stock = stock - ? WHERE idProducto = ?';
    $stmt = $pdo->prepare($sql);

    foreach ($productosSelect as $producto) {
        $stmt->execute([
            (int) $producto['cantidad'],
            (int) $producto['idProducto'],
        ]);
    }

}

function actualizarSaldoTarjeta(int $idTarjeta, float $monto): void
{
    $pdo = db();
    $sql = 'UPDATE tarjeta SET saldo = saldo - ? WHERE idTarjeta = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        (float) $monto,
        (int) $idTarjeta,
    ]);
}
