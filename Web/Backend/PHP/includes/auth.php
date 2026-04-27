<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/helpers.php';

/**
 * Autentica un usuario con su usuario y contraseña
 */
function authenticate(string $usuario, string $contrasena): ?array
{
    try {
        $stmt = db()->prepare('SELECT * FROM empleado WHERE usuario = ?');
        $stmt->execute([$usuario]);
        $empleado = $stmt->fetch();

        if (!$empleado) {
            return null;
        }

        // Intenta con password_verify primero
        if (password_verify($contrasena, $empleado['contrasena'] ?? '')) {
            return $empleado;
        }

        // Fallback para contraseñas sin hash (compatibilidad)
        if (hash_equals($empleado['contrasena'] ?? '', $contrasena)) {
            return $empleado;
        }

        return null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Registra el inicio de sesión del usuario
 */
function loginUser(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_autenticado'] = [
        'idEmpleado' => $usuario['idEmpleado'] ?? null,
        'usuario' => $usuario['usuario'] ?? null,
        'nombre' => $usuario['nombre'] ?? null,
        'rol' => $usuario['rol'] ?? null,
    ];
}

/**
 * Cierra la sesión del usuario
 */
function logoutUser(): void
{
    unset($_SESSION['usuario_autenticado']);
    session_regenerate_id(true);
}

/**
 * Verifica si el usuario está autenticado
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['usuario_autenticado']);
}

/**
 * Obtiene los datos del usuario autenticado
 */
function currentUser(): ?array
{
    return $_SESSION['usuario_autenticado'] ?? null;
}

/**
 * Verifica si el usuario actual tiene alguno de los roles permitidos
 */
function hasRole(array $rolesPermitidos): bool
{
    $usuario = currentUser();
    if (!$usuario) {
        return false;
    }

    return in_array($usuario['rol'] ?? null, $rolesPermitidos, true);
}

