<?php
$rol = (int) ($currentUser['rol'] ?? 0);
$empleados = $state['empleados'] ?? [];
?>

<section class="container employees-section">
    <div class="panel-card panel-heading employees-head">
        <div>
            <span class="badge-soft">Gestión interna</span>
            <h2>Empleados</h2>
            <p>Consulta los usuarios internos y registra nuevos accesos para el panel administrativo.</p>
        </div>

        <div class="role-box">
            <strong><?= $rol === 1 ? 'Administrador' : 'Empleado' ?></strong>
            <span><?= count($empleados) ?> usuarios registrados</span>
        </div>
    </div>

    <div class="employees-layout">
        <section class="panel-card employees-table-card">
            <div class="employees-card-head">
                <h3>Usuarios registrados</h3>
                <span class="employee-count"><?= count($empleados) ?></span>
            </div>

            <div class="table-wrap">
                <table class="employees-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($empleados)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-table">No hay empleados registrados todavía.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($empleados as $empleado): ?>
                                <tr>
                                    <td><?= e((string) ($empleado['usuario'] ?? '')) ?></td>
                                    <td><?= e((string) ($empleado['nombre'] ?? '')) ?></td>
                                    <td><?= e((string) ($empleado['apellido'] ?? '')) ?></td>
                                    <td>
                                        <span class="mini-tag <?= ((int) ($empleado['rol'] ?? 0) === 1) ? 'tag-admin' : 'tag-employee' ?>">
                                            <?= ((int) ($empleado['rol'] ?? 0) === 1) ? 'Admin' : 'Empleado' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="panel-card employees-form-card">
            <h3>Nuevo empleado</h3>
            <p class="employees-form-note">Completa los datos para crear un acceso interno.</p>

            <form method="post" class="stacked-form employees-form">
                <input type="hidden" name="_action" value="guardar_empleado">

                <div>
                    <label for="nombre">Nombre</label>
                    <input id="nombre" type="text" name="nombre" required>
                </div>

                <div>
                    <label for="apellido">Apellido</label>
                    <input id="apellido" type="text" name="apellido" required>
                </div>

                <div>
                    <label for="usuario">Usuario</label>
                    <input id="usuario" type="text" name="usuario" required>
                </div>

                <div>
                    <label for="contrasena">Contraseña</label>
                    <div class="password-row">
                        <input type="password" name="contrasena" id="contrasena" required>
                        <button type="button" id="btnVer" class="btn btn-ghost btn-sm">Ver</button>
                    </div>
                </div>

                <div>
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol" required>
                        <option value="" disabled selected>Seleccione...</option>
                        <option value="1">Admin</option>
                        <option value="2">Empleado</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Agregar empleado</button>
            </form>
        </aside>
    </div>
</section>

<script src="../../../Backend/Js/empleados_service.js"></script>