<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

/**
 * Obtiene todas las categorías ordenadas por nombre
 */
function getCategorias(): array
{
    try {
        $stmt = db()->prepare('SELECT idCategoria, nombreCat FROM categoria ORDER BY nombreCat ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Obtiene todas las marcas ordenadas por nombre
 */
function getMarcas(): array
{
    try {
        $stmt = db()->prepare('SELECT idMarca, nombreMarc FROM marca ORDER BY nombreMarc ASC');
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Obtiene los filtros de catálogo desde la sesión
 */
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

/**
 * Establece los filtros de catálogo en la sesión
 */
function setCatalogFilters(array $filters): void
{
    $_SESSION['catalog_filters'] = $filters;
}

/**
 * Limpia los filtros de catálogo de la sesión
 */
function resetCatalogFilters(): void
{
    unset($_SESSION['catalog_filters']);
}

/**
 * Obtiene productos del catálogo con filtros aplicados
 */
function getProductosCatalogo(array $filters): array
{
    try {
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

        $q = trim((string) ($filters['q'] ?? ''));
        $categoria = (int) ($filters['categoria'] ?? 0);
        $marca = (int) ($filters['marca'] ?? 0);

        if ($q !== '') {
            $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ? OR CAST(p.idProducto AS CHAR) LIKE ?)';
            $search = '%' . $q . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if ($categoria > 0) {
            $sql .= ' AND p.idCategoria = ?';
            $params[] = $categoria;
        }

        if ($marca > 0) {
            $sql .= ' AND p.idMarca = ?';
            $params[] = $marca;
        }

        $sql .= " ORDER BY {$orderBy}";

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
