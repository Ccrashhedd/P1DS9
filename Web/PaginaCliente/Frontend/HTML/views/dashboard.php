<?php
$rol = (int) ($currentUser['rol'] ?? 0);
?>
<section class="container dashboard-section">
    <div class="dashboard-head panel-card">
        <div>
            <span class="badge-soft">Panel interno</span>
            <h2>Bienvenido, <?= e((string) (($currentUser['nombre'] ?? '') !== '' ? $currentUser['nombre'] : $currentUser['usuario'])) ?></h2>
            <p>Desde aquí puedes entrar al área de productos. El administrador tiene permisos para agregar y el empleado puede actualizar stock.</p>
        </div>
        <div class="role-box">
            <strong><?= e((string) ($currentUser['rol_nombre'] ?? '')) ?></strong>
            <span><?= $rol === 1 ? 'Control total de inventario' : 'Gestión operativa de stock' ?></span>
        </div>
    </div>

    <div class="action-grid">
        <article class="panel-card action-card">
            <h3>Administrar productos</h3>
            <p>Entrar al módulo para agregar productos nuevos y actualizar existencias.</p>
            <form method="post">
                <input type="hidden" name="_action" value="go_productos">
                <button type="submit" class="btn btn-primary">Abrir módulo</button>
            </form>
        </article>

        <article class="panel-card action-card">
            <h3>Volver al catálogo</h3>
            <p>Regresa a la vista que verá el cliente sin salir de la ruta principal.</p>
            <form method="post">
                <input type="hidden" name="_action" value="go_catalogo">
                <button type="submit" class="btn btn-ghost">Ir al catálogo</button>
            </form>
        </article>
    </div>
</section>
