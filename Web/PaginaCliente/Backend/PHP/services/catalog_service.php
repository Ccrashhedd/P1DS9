<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

function getCategorias(): array
{
    $categorias = [];
    $result = db()->query('SELECT idCategoria, nombreCat FROM categoria ORDER BY nombreCat ASC');

    while ($fila = $result->fetch_assoc()) {
        $categorias[] = $fila;
    }

    return $categorias;
}

function getMarcas(): array
{
    $marcas = [];
    $result = db()->query('SELECT idMarca, nombreMarc FROM marca ORDER BY nombreMarc ASC');

    while ($fila = $result->fetch_assoc()) {
        $marcas[] = $fila;
    }

    return $marcas;
}

function getCatalogFilters(): array
{
    $defaults = [
        'q' => '',
        'categoria' => 0,
        'marca' => 0,
        'orden' => 'nombre_asc',
    ];

    return array_merge($defaults, $_SESSION['catalog_filters'] ?? []);
}

function setCatalogFilters(array $filters): void
{
    $_SESSION['catalog_filters'] = $filters;
}

function resetCatalogFilters(): void
{
    unset($_SESSION['catalog_filters']);
}

function getProductosCatalogo(array $filters): array
{
    $ordenesPermitidos = [
        'nombre_asc' => 'p.nombre ASC',
        'nombre_desc' => 'p.nombre DESC',
        'precio_asc' => 'p.precioVenta ASC',
        'precio_desc' => 'p.precioVenta DESC',
        'stock_desc' => 'p.stock DESC',
    ];

    $orderBy = $ordenesPermitidos[$filters['orden'] ?? 'nombre_asc'] ?? $ordenesPermitidos['nombre_asc'];

    $sql = '
        SELECT
            p.idProducto,
            p.nombre,
            p.unidad,
            p.descripcion,
            p.stock,
            p.precioVenta,
            p.imagen,
            c.nombreCat AS categoria,
            m.nombreMarc AS marca
        FROM productos p
        INNER JOIN categoria c ON c.idCategoria = p.idCategoria
        INNER JOIN marca m ON m.idMarca = p.idMarca
        WHERE 1=1
    ';

    $params = [];
    $types = '';

    $q = trim((string) ($filters['q'] ?? ''));
    $categoria = (int) ($filters['categoria'] ?? 0);
    $marca = (int) ($filters['marca'] ?? 0);

    if ($q !== '') {
        $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ? OR CAST(p.idProducto AS CHAR) LIKE ?)';
        $search = '%' . $q . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= 'sss';
    }

    if ($categoria > 0) {
        $sql .= ' AND p.idCategoria = ?';
        $params[] = $categoria;
        $types .= 'i';
    }

    if ($marca > 0) {
        $sql .= ' AND p.idMarca = ?';
        $params[] = $marca;
        $types .= 'i';
    }

    $sql .= " ORDER BY {$orderBy}";

    $stmt = db()->prepare($sql);

    if ($params !== []) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $productos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }

    $stmt->close();

    return $productos;
}
