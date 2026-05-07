<?php
$state = $state ?? [];
$rol = (int) ($currentUser['rol'] ?? 0);
$puedeAgregarProductos = $rol === 1;
$puedeActualizarStock = $rol === 1;
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
        <?php if ($puedeAgregarProductos): ?>
            <section class="panel-card">
                <h3>Agregar producto</h3>
                <form method="post" id="form-producto" class="stacked-form compact-grid" enctype="multipart/form-data">
                    <input type="hidden" name="_action" id="producto-action" value="guardar_producto">
                    <input type="hidden" name="imagen" id="imagenHidden" value="">

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
                        <label for="imagenFile">Imagen</label>
                        <input id="imagenFile" name="imagenFile" type="file" accept="image/*">
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
                        <button type="submit" class="btn btn-primary" id="productoSubmit">Guardar producto</button>
                        <button type="button" class="btn btn-ghost" id="productoCancel" style="display:none;margin-left:8px;">Cancelar</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>

        <?php if($rol == 1): ?>
            <section class="panel-card">
                <h3>Categorías</h3>
                <form method="post" class="stacked-form" id="form-categoria">
                    <input type="hidden" name="_action" id="categoria-action" value="guardar_categoria">
                    <input type="hidden" name="idCategoria" id="admin_idCategoria">
                    <label for="categoriaSelect">Seleccione</label>
                    <select id="categoriaSelect" name="categoriaSelect">
                        <option value="">Seleccione</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria['idCategoria'] ?>"><?= e((string) $categoria['nombreCat']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="categoriaInput">Nombre</label>
                    <input type="text" id="categoriaInput" name="categoria" maxlength="80">
                    <div>
                        <button type="submit" class="btn btn-primary" id="categoriaSubmit">Agregar</button>
                        <button type="button" class="btn btn-ghost" id="categoriaCancel" style="display:none;">Cancelar</button>
                    </div>
                </form>

                <h3 style="margin-top:18px;">Marcas</h3>
                <form method="post" class="stacked-form" id="form-marca">
                    <input type="hidden" name="_action" id="marca-action" value="guardar_marca">
                    <input type="hidden" name="idMarca" id="admin_idMarca">
                    <label for="marcaSelect">Seleccione</label>
                    <select id="marcaSelect" name="marcaSelect">
                        <option value="">Seleccione</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?= (int) $marca['idMarca'] ?>"><?= e((string) $marca['nombreMarc']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="marcaInput">Nombre</label>
                    <input type="text" id="marcaInput" name="marca" maxlength="80">
                    <div>
                        <button type="submit" class="btn btn-primary" id="marcaSubmit">Agregar</button>
                        <button type="button" class="btn btn-ghost" id="marcaCancel" style="display:none;">Cancelar</button>
                    </div>
                </form>
            </section>
        <?php endif; ?>
    </div>

    <section class="panel-card table-card">
        <input type="text" id="searchInput" placeholder="Buscar productos..." class="search-input">
        <button id="clearSearch" class="btn btn-ghost btn-sm">Limpiar</button>
        <h3>Listado general</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Acciones</th>
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
                            <td>
                                <button type="button" class="btn btn-ghost btn-sm js-edit-product"
                                    data-id="<?= e((string) $producto['idProducto']) ?>"
                                    data-nombre="<?= e((string) $producto['nombre']) ?>"
                                    data-unidad="<?= e((string) ($producto['unidad'] ?? '')) ?>"
                                    data-descripcion="<?= e((string) ($producto['descripcion'] ?? '')) ?>"
                                    data-stock="<?= (int) $producto['stock'] ?>"
                                    data-preciocosto="<?= htmlspecialchars((string) $producto['precioCosto'], ENT_QUOTES) ?>"
                                    data-precioventa="<?= htmlspecialchars((string) $producto['precioVenta'], ENT_QUOTES) ?>"
                                    data-idcategoria="<?= (int) ($producto['idCategoria'] ?? 0) ?>"
                                    data-idmarca="<?= (int) ($producto['idMarca'] ?? 0) ?>"
                                    data-imagen="<?= e((string) ($producto['imagen'] ?? '')) ?>"
                                >Editar</button>
                            </td>
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

<script src="../../../Backend/Js/input_service.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Validaciones de inputs
    const idProducto = document.getElementById('idProducto');
    const unidad = document.getElementById('unidad');
    const stock = document.getElementById('stock');
    const nombre = document.getElementById('nombre');
    const descripcion = document.getElementById('descripcion');
    const precioCosto = document.getElementById('precioCosto');
    const precioVenta = document.getElementById('precioVenta');

    function onlyDigitsListener(e) {
        const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        if (!/^[0-9]$/.test(e.key)) e.preventDefault();
    }

    if (idProducto) idProducto.addEventListener('keydown', onlyDigitsListener);
    if (unidad) unidad.addEventListener('keydown', onlyDigitsListener);
    if (stock) stock.addEventListener('keydown', onlyDigitsListener);

    function nameAllowListener(e) {
        const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        if (!/^[A-Za-z0-9\-\/\.\(\) ]$/.test(e.key)) e.preventDefault();
    }

    if (nombre) nombre.addEventListener('keydown', nameAllowListener);
    if (descripcion) descripcion.addEventListener('keydown', nameAllowListener);

    if (precioCosto) precioCosto.addEventListener('keydown', window.validarPrecio);
    if (precioVenta) precioVenta.addEventListener('keydown', window.validarPrecio);

    // Buscador de tabla
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const table = document.querySelector('.table-wrap table tbody');

    function applyFilter() {
        const q = (searchInput.value || '').toLowerCase().trim();
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            applyFilter();
        });
    }

    // Categoria / Marca: alternar add / edit
    const categoriaSelect = document.getElementById('categoriaSelect');
    const categoriaInput = document.getElementById('categoriaInput');
    const categoriaAction = document.getElementById('categoria-action');
    const adminCategoriaId = document.getElementById('admin_idCategoria');
    const categoriaSubmit = document.getElementById('categoriaSubmit');
    const categoriaCancel = document.getElementById('categoriaCancel');

    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', () => {
            const val = categoriaSelect.value;
            if (val) {
                const opt = categoriaSelect.selectedOptions[0];
                categoriaInput.value = opt.textContent.trim();
                categoriaAction.value = 'actualizar_categoria';
                adminCategoriaId.value = val;
                categoriaSubmit.textContent = 'Editar';
                categoriaCancel.style.display = '';
            } else {
                categoriaInput.value = '';
                categoriaAction.value = 'guardar_categoria';
                adminCategoriaId.value = '';
                categoriaSubmit.textContent = 'Agregar';
                categoriaCancel.style.display = 'none';
            }
        });
    }

    if (categoriaCancel) {
        categoriaCancel.addEventListener('click', () => {
            categoriaSelect.value = '';
            categoriaSelect.dispatchEvent(new Event('change'));
        });
    }

    const marcaSelect = document.getElementById('marcaSelect');
    const marcaInput = document.getElementById('marcaInput');
    const marcaAction = document.getElementById('marca-action');
    const adminMarcaId = document.getElementById('admin_idMarca');
    const marcaSubmit = document.getElementById('marcaSubmit');
    const marcaCancel = document.getElementById('marcaCancel');

    if (marcaSelect) {
        marcaSelect.addEventListener('change', () => {
            const val = marcaSelect.value;
            if (val) {
                const opt = marcaSelect.selectedOptions[0];
                marcaInput.value = opt.textContent.trim();
                marcaAction.value = 'actualizar_marca';
                adminMarcaId.value = val;
                marcaSubmit.textContent = 'Editar';
                marcaCancel.style.display = '';
            } else {
                marcaInput.value = '';
                marcaAction.value = 'guardar_marca';
                adminMarcaId.value = '';
                marcaSubmit.textContent = 'Agregar';
                marcaCancel.style.display = 'none';
            }
        });
    }

    if (marcaCancel) {
        marcaCancel.addEventListener('click', () => {
            marcaSelect.value = '';
            marcaSelect.dispatchEvent(new Event('change'));
        });
    }

    // Edit product from table
    const editButtons = document.querySelectorAll('.js-edit-product');
    const formProducto = document.getElementById('form-producto');
    const productoAction = document.getElementById('producto-action');
    const productoSubmit = document.getElementById('productoSubmit');
    const productoCancel = document.getElementById('productoCancel');
    const imagenHidden = document.getElementById('imagenHidden');
    const prodCategoria = document.getElementById('idCategoria');
    const prodMarca = document.getElementById('idMarca');

    function setAddMode() {
        productoAction.value = 'guardar_producto';
        productoSubmit.textContent = 'Guardar producto';
        productoCancel.style.display = 'none';
        idProducto.readOnly = false;
        formProducto.reset();
        imagenHidden.value = '';
        if (prodCategoria) prodCategoria.value = '';
        if (prodMarca) prodMarca.value = '';
    }

    function setEditMode(data) {
        productoAction.value = 'actualizar_producto';
        productoSubmit.textContent = 'Actualizar producto';
        productoCancel.style.display = '';
        idProducto.value = data.id;
        idProducto.readOnly = true;
        unidad.value = data.unidad || 'UND';
        nombre.value = data.nombre || '';
        descripcion.value = data.descripcion || '';
        stock.value = data.stock || 0;
        precioCosto.value = data.preciocosto || '';
        precioVenta.value = data.precioventa || '';
        if (data.idcategoria && prodCategoria) prodCategoria.value = data.idcategoria;
        if (data.idmarca && prodMarca) prodMarca.value = data.idmarca;
        imagenHidden.value = data.imagen || '';
    }

    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const data = {
                id: btn.dataset.id,
                nombre: btn.dataset.nombre,
                unidad: btn.dataset.unidad,
                descripcion: btn.dataset.descripcion,
                stock: btn.dataset.stock,
                preciocosto: btn.dataset.preciocosto,
                precioventa: btn.dataset.precioventa,
                idcategoria: btn.dataset.idcategoria,
                idmarca: btn.dataset.idmarca,
                imagen: btn.dataset.imagen,
            };

            setEditMode(data);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    if (productoCancel) {
        productoCancel.addEventListener('click', () => setAddMode());
    }
});
</script>