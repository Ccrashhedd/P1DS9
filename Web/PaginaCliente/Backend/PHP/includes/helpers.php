<?php

declare(strict_types=1);

function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function money(float $valor): string
{
    return '$' . number_format($valor, 2);
}

function roleName(int $rol): string
{
    return match ($rol) {
        1 => 'Administrador',
        2 => 'Empleado',
        default => 'Desconocido',
    };
}

function currentView(): string
{
    return $_SESSION['app_view'] ?? 'catalogo';
}

function setCurrentView(string $view): void
{
    $_SESSION['app_view'] = $view;
}

function flash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }

    $value = (string) $_SESSION[$key];
    unset($_SESSION[$key]);

    return $value;
}

function setFlash(string $key, string $message): void
{
    $_SESSION[$key] = $message;
}

function placeholderSvg(string $texto): string
{
    $textoSeguro = rawurlencode($texto);
    return "data:image/svg+xml;charset=UTF-8," .
        "<svg xmlns='http://www.w3.org/2000/svg' width='800' height='500'>" .
        "<rect width='100%' height='100%' fill='%23eceff3'/>" .
        "<text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='%23606b7a' font-family='Arial' font-size='24'>" .
        $textoSeguro .
        '</text></svg>';
}
