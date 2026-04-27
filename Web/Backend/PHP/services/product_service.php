<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

/**
 * Obtiene todos los productos con información de categoría y marca
 */
function getProductosPanel(): array
{
    try {
        $sql = '
            SELECT p.idProducto, p.nombre, p.stock, p.precioCosto, p.precioVenta,
                   c.nombreCat AS categoria, m.nombreMarc AS marca
            FROM productos p
            INNER JOIN categoria c ON c.idCategoria = p.idCategoria
            INNER JOIN marca m ON m.idMarca = p.idMarca
            ORDER BY p.nombre ASC
        ';
        $stmt = db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Guarda un nuevo producto
 */
function guardarProducto(array $data): void
{
    try {
        // Verificar si el producto ya existe
        $check = db()->prepare('SELECT idProducto FROM productos WHERE idProducto = ? LIMIT 1');
        $check->execute([$data['idProducto']]);
        $existe = $check->fetch();

        if ($existe) {
            throw new RuntimeException('Ya existe un producto con ese código.');
        }

        // Insertar el nuevo producto
        $stmt = db()->prepare('
            INSERT INTO productos (idProducto, nombre, unidad, descripcion, stock, precioCosto, precioVenta, imagen, idCategoria, idMarca)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['idProducto'],
            $data['nombre'],
            $data['unidad'],
            $data['descripcion'],
            (int) $data['stock'],
            (float) $data['precioCosto'],
            (float) $data['precioVenta'],
            $data['imagen'],
            (int) $data['idCategoria'],
            (int) $data['idMarca'],
        ]);
    } catch (PDOException $e) {
        throw new RuntimeException('No se pudo guardar el producto en la base de datos.');
    }
}

/**
 * Actualiza el stock de un producto
 */
function actualizarStockProducto(string $idProducto, int $stock): void
{
    try {
        $stmt = db()->prepare('UPDATE productos SET stock = ? WHERE idProducto = ?');
        $stmt->execute([$stock, $idProducto]);
    } catch (PDOException $e) {
        throw new RuntimeException('No se pudo actualizar el stock.');
    }
}
