<?php
$rol = (int) ($currentUser['rol'] ?? 0);
$categorias = $state['panel']['categorias'];
$marcas = $state['panel']['marcas'];
$productos = $state['panel']['productos'];
?>
<section class="container products-panel-section">
    <div class="panel-card panel-heading">
        <div>
            <span class="badge-soft">Gestión de inventario</span>
            <h2>Productos y stock</h2>
            <p>El campo del código del producto queda listo para usar un lector de código de barras conectado por USB, ya que esos lectores normalmente escriben como si fueran teclado.</p>
        </div>
        <form method="post">
            <input type="hidden" name="_action" value="go_dashboard">
            <button type="submit" class="btn btn-ghost">Volver al dashboard</button>
        </form>
    </div>

    <div class="panel-columns">
        <?php if ($rol === 1): ?>
            <section class="panel-card">
                <h3>Agregar producto</h3>
                <form method="post" class="stacked-form compact-grid">
                    <input type="hidden" name="_action" value="guardar_producto">

                    <div>
                        <label for="idProducto">Código del producto</label>
                        <input id="idProducto" name="idProducto" type="text" inputmode="numeric" autocomplete="off" required>
                    </div>

                    <div>
                        <label for="unidad">Unidad</label>
                        <input id="unidad" name="unidad" type="text" value="UND" maxlength="20" required>
                    </div>

                    <div class="full-span">
                        <label for="nombre">Nombre</label>
                        <input id="nombre" name="nombre" type="text" maxlength="50" required>
                    </div>

                    <div class="full-span">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" maxlength="250" required></textarea>
                    </div>

                    <div>
                        <label for="stock">Stock inicial</label>
                        <input id="stock" name="stock" type="number" min="0" step="1" required>
                    </div>

                    <div>
                        <label for="precioCosto">Precio costo</label>
                        <input id="precioCosto" name="precioCosto" type="number" min="0" step="0.01" required>
                    </div>

                    <div>
                        <label for="precioVenta">Precio venta</label>
                        <input id="precioVenta" name="precioVenta" type="number" min="0" step="0.01" required>
                    </div>

                    <div>
                        <label for="imagen">Imagen</label>
                        <input id="imagen" name="imagen" type="text" maxlength="500" placeholder="ejemplo.jpg">
                    </div>

                    <div>
                        <label for="idCategoria">Categoría</label>
                        <select id="idCategoria" name="idCategoria" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= (int) $categoria['idCategoria'] ?>"><?= e((string) $categoria['nombreCat']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="idMarca">Marca</label>
                        <select id="idMarca" name="idMarca" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?= (int) $marca['idMarca'] ?>"><?= e((string) $marca['nombreMarc']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="full-span">
                        <button type="submit" class="btn btn-primary">Guardar producto</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <section class="panel-card">
            <h3>Actualizar stock</h3>
            <form method="post" class="stacked-form">
                <input type="hidden" name="_action" value="actualizar_stock">
                <label for="stock_idProducto">Código del producto</label>
                <input id="stock_idProducto" name="idProducto" type="text" inputmode="numeric" autocomplete="off" required>

                <label for="nuevoStock">Nuevo stock</label>
                <input id="nuevoStock" name="stock" type="number" min="0" step="1" required>

                <button type="submit" class="btn btn-primary">Actualizar stock</button>
            </form>
        </section>
    </div>

    <section class="panel-card table-card">
        <h3>Listado general</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Stock</th>
                        <th>Costo</th>
                        <th>Venta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= e((string) $producto['idProducto']) ?></td>
                            <td><?= e((string) $producto['nombre']) ?></td>
                            <td><?= e((string) $producto['categoria']) ?></td>
                            <td><?= e((string) $producto['marca']) ?></td>
                            <td><?= (int) $producto['stock'] ?></td>
                            <td><?= money((float) $producto['precioCosto']) ?></td>
                            <td><?= money((float) $producto['precioVenta']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
