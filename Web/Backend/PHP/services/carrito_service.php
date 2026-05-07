<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

/**
 * Obtiene productos del carrito desde la BD
 */
function getCarritoItems(array $productosSelect): array
{
    if (empty($productosSelect)) {
        return [];
    }

    try {

        $ids = [];

        foreach ($productosSelect as $item) {

            $id = (int) ($item['idProducto'] ?? 0);

            if ($id > 0) {
                $ids[] = $id;
            }
        }

        $ids = array_unique($ids);

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "
            SELECT
                idProducto,
                nombre,
                precioVenta,
                stock
            FROM productos
            WHERE idProducto IN ($placeholders)
        ";

        $stmt = db()->prepare($sql);

        $stmt->execute($ids);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Throwable $e) {

        return [];
    }
}

/**
 * Inserta una tarjeta nueva
 */
function ingresarTarjeta(string $digitos, string $cvv, string $fecha): int
{
    $digitos = trim($digitos);
    $cvv = trim($cvv);
    $fecha = trim($fecha);

    // DEBUG
    error_log('DIGITOS RECIBIDOS => ' . $digitos);
    error_log('CVV RECIBIDO => ' . $cvv);
    error_log('FECHA RECIBIDA => ' . $fecha);

    if ($digitos === '') {
        return 0;
    }

    try {

        $pdo = db();

        // Tipo random
        $tipo = mt_rand(0, 1) ? 'credito' : 'debito';

        // Saldo base
        $saldo = 3000.00;

        // Solo credito tiene saldo maximo
        $saldoMaximo = null;

        if ($tipo === 'credito') {

            $saldoMaximo = mt_rand(2000, 10000);
        }

        // Convertir MM/AA a YYYY-MM-01
        $fechaMysql = null;

        if ($fecha !== '') {

            $partes = explode('/', $fecha);

            if (count($partes) === 2) {

                $mes = trim($partes[0]);
                $anio = trim($partes[1]);

                if (
                    ctype_digit($mes)
                    && ctype_digit($anio)
                ) {

                    $mesNumero = (int) $mes;

                    if ($mesNumero >= 1 && $mesNumero <= 12) {

                        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

                        $fechaMysql = '20' . $anio . '-' . $mes . '-01';
                    }
                }
            }
        }

        $sql = "
            INSERT INTO tarjeta (
                tipo,
                digitos,
                codSeguridad,
                fechaVence,
                saldo,
                saldoMaximo
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $tipo,
            $digitos,
            $cvv !== '' ? $cvv : null,
            $fechaMysql,
            $saldo,
            $saldoMaximo
        ]);

        return (int) $pdo->lastInsertId();

    } catch (Throwable $e) {

        error_log('ERROR ingresarTarjeta => ' . $e->getMessage());

        return 0;
    }
}

/**
 * Normaliza productos
 */
function normalizarProductosPago(array $productosSelect): array
{
    $productosDb = getCarritoItems($productosSelect);

    if (empty($productosDb)) {
        return [];
    }

    $cantidades = [];

    foreach ($productosSelect as $producto) {

        $idProducto = (int) ($producto['idProducto'] ?? 0);

        $cantidad = (int) ($producto['cantidad'] ?? 0);

        if ($idProducto > 0 && $cantidad > 0) {

            if (!isset($cantidades[$idProducto])) {
                $cantidades[$idProducto] = 0;
            }

            $cantidades[$idProducto] += $cantidad;
        }
    }

    $resultado = [];

    foreach ($productosDb as $productoDb) {

        $idProducto = (int) $productoDb['idProducto'];

        if (!isset($cantidades[$idProducto])) {
            continue;
        }

        $resultado[] = [
            'idProducto' => $idProducto,
            'nombre' => (string) $productoDb['nombre'],
            'precioUnitario' => (float) $productoDb['precioVenta'],
            'cantidad' => (int) $cantidades[$idProducto],
            'stock' => (int) $productoDb['stock'],
        ];
    }

    return $resultado;
}

/**
 * Procesa el pago
 */
function pagoCarrito(array $productosSelect, array $pagoData): string
{
    $productos = normalizarProductosPago($productosSelect);

    if (empty($productos)) {
        return 'El carrito esta vacio.';
    }

    $idTarjeta = (int) ($pagoData['idTarjeta'] ?? 0);

    if ($idTarjeta <= 0) {
        return 'Tarjeta invalida.';
    }

    $subtotal = 0;

    foreach ($productos as $producto) {

        if ($producto['cantidad'] > $producto['stock']) {

            return 'Stock insuficiente para el producto: ' . $producto['nombre'];
        }

        $subtotal += (
            $producto['cantidad']
            * $producto['precioUnitario']
        );
    }

    $itbms = $subtotal * 0.07;

    $total = $subtotal + $itbms;

    $pdo = db();

    try {

        $pdo->beginTransaction();

        $sqlTarjeta = "
            SELECT
                idTarjeta,
                tipo,
                saldo,
                saldoMaximo
            FROM tarjeta
            WHERE idTarjeta = ?
            FOR UPDATE
        ";

        $stmtTarjeta = $pdo->prepare($sqlTarjeta);

        $stmtTarjeta->execute([$idTarjeta]);

        $tarjeta = $stmtTarjeta->fetch(PDO::FETCH_ASSOC);

        if (!$tarjeta) {

            $pdo->rollBack();

            return 'La tarjeta no existe.';
        }

        $tipo = (string) $tarjeta['tipo'];

        $saldo = (float) $tarjeta['saldo'];

        $saldoMaximo = $tarjeta['saldoMaximo'] !== null
            ? (float) $tarjeta['saldoMaximo']
            : 0;

        $saldoDisponible = $saldo;

        if ($tipo === 'credito') {
            $saldoDisponible += $saldoMaximo;
        }

        if ($saldoDisponible < $total) {

            $pdo->rollBack();

            return 'Saldo insuficiente.';
        }

        $nuevoSaldo = $saldo;

        $nuevoSaldoMaximo = $saldoMaximo;

        if ($total <= $saldo) {

            $nuevoSaldo = $saldo - $total;

        } else {

            $restante = $total - $saldo;

            $nuevoSaldo = 0;

            if ($tipo === 'credito') {

                $nuevoSaldoMaximo -= $restante;
            }
        }

        // FACTURA
        $sqlFactura = "
            INSERT INTO factura (
                idTarjeta,
                subtotal,
                itbms,
                total
            )
            VALUES (?, ?, ?, ?)
        ";

        $stmtFactura = $pdo->prepare($sqlFactura);

        $stmtFactura->execute([
            $idTarjeta,
            $subtotal,
            $itbms,
            $total
        ]);

        $idFactura = (int) $pdo->lastInsertId();

        // DETALLES
        $sqlDetalle = "
            INSERT INTO factura_detalle (
                idFactura,
                idProducto,
                cantidad,
                precio_unitario
            )
            VALUES (?, ?, ?, ?)
        ";

        $stmtDetalle = $pdo->prepare($sqlDetalle);

        foreach ($productos as $producto) {

            $stmtDetalle->execute([
                $idFactura,
                $producto['idProducto'],
                $producto['cantidad'],
                $producto['precioUnitario']
            ]);
        }

        // DESCONTAR STOCK
        $sqlStock = "
            UPDATE productos
            SET stock = stock - ?
            WHERE idProducto = ?
        ";

        $stmtStock = $pdo->prepare($sqlStock);

        foreach ($productos as $producto) {

            $stmtStock->execute([
                $producto['cantidad'],
                $producto['idProducto']
            ]);
        }

        // ACTUALIZAR TARJETA
        $sqlUpdateTarjeta = "
            UPDATE tarjeta
            SET
                saldo = ?,
                saldoMaximo = ?
            WHERE idTarjeta = ?
        ";

        $stmtUpdateTarjeta = $pdo->prepare($sqlUpdateTarjeta);

        $stmtUpdateTarjeta->execute([
            $nuevoSaldo,
            $tipo === 'credito'
                ? $nuevoSaldoMaximo
                : null,
            $idTarjeta
        ]);

        $pdo->commit();

        return 'Pago realizado con éxito. Factura #' . $idFactura;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return 'Error al procesar el pago: ' . $e->getMessage();
    }
}