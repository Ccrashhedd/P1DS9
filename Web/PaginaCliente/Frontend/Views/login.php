<section class="container auth-section">
    <div class="auth-layout">
        <div class="auth-side">
            <span class="badge-soft badge-on-dark">Acceso interno</span>
            <h2>Iniciar sesión</h2>
            <p>Los empleados y el administrador entran desde aquí para trabajar con el inventario y, más adelante, con el lector de código de barras y los microservicios.</p>
            <ul class="feature-list">
                <li>Administrador: agrega productos y actualiza inventario.</li>
                <li>Empleado: actualiza stock y consulta productos.</li>
                <li>La URL visible se mantiene siempre en index.php.</li>
            </ul>
        </div>

        <div class="auth-card">
            <h3>Ingresa tus credenciales</h3>
            <form method="post" class="stacked-form">
                <input type="hidden" name="_action" value="login">

                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" type="text" maxlength="20" required>

                <label for="contrasena">Contraseña</label>
                <input id="contrasena" name="contrasena" type="password" maxlength="50" required>

                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>

            <div class="demo-box">
                <strong>Usuarios de prueba</strong>
                <p>admin / admin123</p>
                <p>empleado / empleado123</p>
            </div>
        </div>
    </div>
</section>
