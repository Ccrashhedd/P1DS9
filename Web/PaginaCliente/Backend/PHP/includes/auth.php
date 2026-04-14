<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/helpers.php';

function currentUser(): ?array
{
    return $_SESSION['usuario_autenticado'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function userRole(): int
{
    return (int) (currentUser()['rol'] ?? 0);
}

function loginUser(array $usuario): void
{
    session_regenerate_id(true);

    $_SESSION['usuario_autenticado'] = [
        'usuario' => $usuario['usuario'],
        'nombre' => $usuario['nombre'] ?? '',
        'apellido' => $usuario['apellido'] ?? '',
        'rol' => (int) ($usuario['rol'] ?? 0),
        'rol_nombre' => roleName((int) ($usuario['rol'] ?? 0)),
    ];
}

function logoutUser(): void
{
    unset($_SESSION['usuario_autenticado']);
    session_regenerate_id(true);
}

function hasRole(array $rolesPermitidos): bool
{
    return isLoggedIn() && in_array(userRole(), $rolesPermitidos, true);
}

function authenticate(string $usuario, string $contrasena): ?array
{
    $stmt = db()->prepare('SELECT usuario, nombre, apellido, rol, contrasena FROM empleado WHERE usuario = ? LIMIT 1');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $empleado = $resultado->fetch_assoc();
    $stmt->close();

    if (!$empleado) {
        return null;
    }

    $contrasenaGuardada = (string) $empleado['contrasena'];
    $valida = false;

    if (password_get_info($contrasenaGuardada)['algo'] !== null) {
        $valida = password_verify($contrasena, $contrasenaGuardada);
    } else {
        $valida = hash_equals($contrasenaGuardada, $contrasena);
    }

    return $valida ? $empleado : null;
}
