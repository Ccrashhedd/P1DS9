<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

function getProductosPanel(): array
{
    $productos = [];
    $sql = '
        SELECT p.idProducto, p.nombre, p.stock, p.precioCosto, p.precioVenta,
               c.nombreCat AS categoria, m.nombreMarc AS marca
        FROM productos p
        INNER JOIN categoria c ON c.idCategoria = p.idCategoria
        INNER JOIN marca m ON m.idMarca = p.idMarca
        ORDER BY p.nombre ASC
    ';
    $result = db()->query($sql);

    while ($fila = $result->fetch_assoc()) {
        $productos[] = $fila;
    }

    return $productos;
}

function guardarProducto(array $data): void
{
    $conexion = db();

    $check = $conexion->prepare('SELECT idProducto FROM productos WHERE idProducto = ? LIMIT 1');
    $check->bind_param('s', $data['idProducto']);
    $check->execute();
    $existe = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existe) {
        throw new RuntimeException('Ya existe un producto con ese código.');
    }

    $stmt = $conexion->prepare('
        INSERT INTO productos (idProducto, nombre, unidad, descripcion, stock, precioCosto, precioVenta, imagen, idCategoria, idMarca)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->bind_param(
        'ssssiddsii',
        $data['idProducto'],
        $data['nombre'],
        $data['unidad'],
        $data['descripcion'],
        $data['stock'],
        $data['precioCosto'],
        $data['precioVenta'],
        $data['imagen'],
        $data['idCategoria'],
        $data['idMarca']
    );
    $stmt->execute();
    $stmt->close();
}

function actualizarStockProducto(string $idProducto, int $stock): void
{
    $stmt = db()->prepare('UPDATE productos SET stock = ? WHERE idProducto = ?');
    $stmt->bind_param('is', $stock, $idProducto);
    $stmt->execute();
    $stmt->close();
}
