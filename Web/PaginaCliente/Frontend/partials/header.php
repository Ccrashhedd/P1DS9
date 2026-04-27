<?php
$usuarioNombre = trim((string) (($currentUser['nombre'] ?? '') . ' ' . ($currentUser['apellido'] ?? '')));
$usuarioNombre = $usuarioNombre !== '' ? $usuarioNombre : ($currentUser['usuario'] ?? '');
?>
<header class="topbar-wrap">
    <div class="topbar container">
        <div>
            <h1>ElvisTech</h1>
            <p>Todo en piezas baratas de computacion</p>
        </div>

        <div class="topbar-actions">
            <form method="post">
                <input type="hidden" name="_action" value="go_catalogo">
                <button type="submit" class="btn btn-light">Catálogo</button>
            </form>

            <?php if ($currentUser): ?>
                <form method="post">
                    <input type="hidden" name="_action" value="go_dashboard">
                    <button type="submit" class="btn btn-light">Dashboard</button>
                </form>

                <form method="post">
                    <input type="hidden" name="_action" value="go_productos">
                    <button type="submit" class="btn btn-light">Productos</button>
                </form>
                <form method="post">
                    <input type="hidden" name="_action" value="go_carrito">
                    <button type="submit" class="btn btn-light">Carrito</button>
                </form>

                <div class="user-chip">
                    <strong><?= e((string) $usuarioNombre) ?></strong>
                    <span><?= e((string) ($currentUser['rol_nombre'] ?? '')) ?></span>
                </div>

                <form method="post">
                    <input type="hidden" name="_action" value="logout">
                    <button type="submit" class="btn btn-outline-light">Cerrar sesión</button>
                </form>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="_action" value="go_login">
                    <button type="submit" class="btn btn-outline-light">Acceso empleados</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</header>
