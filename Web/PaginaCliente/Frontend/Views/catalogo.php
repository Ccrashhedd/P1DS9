<?php
$filters = $state['catalog']['filters'];
$categorias = $state['catalog']['categorias'];
$marcas = $state['catalog']['marcas'];
$productos = $state['catalog']['productos'];
?>
<section class="container hero-section">
    <div class="hero-card">
        <div>
            <h2>Catálogo de productos</h2>
            <p>Los clientes pueden ver los productos disponibles mientras el personal autorizado entra por el acceso interno.</p>
        </div>
        <div class="stats-grid">
            <div class="stat-box">
                <strong><?= count($productos) ?></strong>
                <span>Productos mostrados</span>
            </div>
            <div class="stat-box">
                <strong><?= count($categorias) ?></strong>
                <span>Categorías</span>
            </div>
            <div class="stat-box">
                <strong><?= count($marcas) ?></strong>
                <span>Marcas</span>
            </div>
        </div>
    </div>
</section>

<section class="container catalog-layout">
    <aside class="panel-card filter-card">
        <h3>Filtros</h3>
        <form method="post" class="stacked-form">
            <input type="hidden" name="_action" value="catalog_filter">

            <label for="q">Buscar</label>
            <input id="q" name="q" type="text" value="<?= e((string) $filters['q']) ?>" placeholder="Nombre, descripción o código">

            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria">
                <option value="0">Todas</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int) $categoria['idCategoria'] ?>" <?= (int) $filters['categoria'] === (int) $categoria['idCategoria'] ? 'selected' : '' ?>>
                        <?= e((string) $categoria['nombreCat']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="marca">Marca</label>
            <select id="marca" name="marca">
                <option value="0">Todas</option>
                <?php foreach ($marcas as $marca): ?>
                    <option value="<?= (int) $marca['idMarca'] ?>" <?= (int) $filters['marca'] === (int) $marca['idMarca'] ? 'selected' : '' ?>>
                        <?= e((string) $marca['nombreMarc']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="orden">Ordenar</label>
            <select id="orden" name="orden">
                <option value="nombre_asc" <?= ($filters['orden'] ?? '') === 'nombre_asc' ? 'selected' : '' ?>>Nombre A-Z</option>
                <option value="nombre_desc" <?= ($filters['orden'] ?? '') === 'nombre_desc' ? 'selected' : '' ?>>Nombre Z-A</option>
                <option value="precio_asc" <?= ($filters['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio menor a mayor</option>
                <option value="precio_desc" <?= ($filters['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio mayor a menor</option>
                <option value="stock_desc" <?= ($filters['orden'] ?? '') === 'stock_desc' ? 'selected' : '' ?>>Mayor stock</option>
            </select>

            <button type="submit" class="btn btn-primary">Aplicar filtros</button>
        </form>

        <form method="post">
            <input type="hidden" name="_action" value="catalog_reset">
            <button type="submit" class="btn btn-ghost btn-full">Limpiar filtros</button>
        </form>
    </aside>

    <div class="products-area">
        <?php if ($productos === []): ?>
            <div class="empty-card">
                <h3>No se encontraron productos</h3>
                <p>Prueba con otros filtros o revisa que la base de datos esté cargada.</p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($productos as $producto): ?>
                    <?php
                    $imagen = trim((string) ($producto['imagen'] ?? ''));
                    $src = $imagen !== '' ? '../../../Assets/productos/' . rawurlencode($imagen) : placeholderSvg((string) $producto['nombre']);
                    $stock = (int) $producto['stock'];
                    ?>
                    <article class="product-card">
                        <div class="product-image-wrap">
                            <img src="<?= e($src) ?>" alt="<?= e((string) $producto['nombre']) ?>" onerror="this.src='<?= e(placeholderSvg((string) $producto['nombre'])) ?>'">
                        </div>
                        <div class="product-body">
                            <span class="mini-tag"><?= e((string) $producto['categoria']) ?></span>
                            <h3><?= e((string) $producto['nombre']) ?></h3>
                            <p class="product-brand"><?= e((string) $producto['marca']) ?> | Código: <?= e((string) $producto['idProducto']) ?></p>
                            <p class="product-desc"><?= e((string) $producto['descripcion']) ?></p>
                            <div class="product-meta">
                                <strong><?= money((float) $producto['precioVenta']) ?></strong>
                                <span class="stock <?= $stock > 0 ? 'in-stock' : 'out-stock' ?>">
                                    <?= $stock > 0 ? 'Stock: ' . $stock : 'Sin stock' ?>
                                </span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<dialog>
    <form method="dialog">
        <h3>Producto agregado al carrito</h3>
        <p>El producto se ha agregado correctamente al carrito de compras.</p>
        <button class="btn btn-primary">Continuar comprando</button>
        <button class="btn btn-ghost" formaction="?_action=go_carrito">Ir al carrito</button>
    </form>
</dialog>
