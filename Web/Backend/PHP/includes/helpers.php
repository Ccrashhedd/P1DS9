<?php

declare(strict_types=1);

/**
 * Escapa HTML para prevenir XSS
 */
function e(?string $text): string
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formatea un número como dinero
 */
function money(float $amount): string
{
    return '$' . number_format($amount, 2, '.', ',');
}

/**
 * Obtiene el nombre del rol por ID
 */
function roleName(int $idRol): string
{
    $roles = [
        1 => 'Administrador',
        2 => 'Empleado',
        3 => 'Cliente',
    ];
    return $roles[$idRol] ?? 'Desconocido';
}

/**
 * Obtiene la vista actual desde la sesión
 */
function currentView(): string
{
    return $_SESSION['current_view'] ?? 'catalogo';
}

/**
 * Establece la vista actual en la sesión
 */
function setCurrentView(string $view): void
{
    $_SESSION['current_view'] = $view;
}

/**
 * Obtiene un mensaje flash de la sesión y lo elimina
 */
function flash(string $key): ?string
{
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $value;
}

/**
 * Establece un mensaje flash en la sesión
 */
function setFlash(string $key, string $value): void
{
    $_SESSION[$key] = $value;
}

/**
 * Obtiene un placeholder SVG
 */
function placeholderSvg(string $size = '200x200'): string
{
    return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size'%3E%3Crect fill='%23ddd' width='100%25' height='100%25'/%3E%3Ctext x='50%25' y='50%25' font-size='14' fill='%23999' text-anchor='middle' dy='.3em'%3EPlaceholder%3C/text%3E%3C/svg%3E";
}

/**
 * Redirige al index.php
 */
// Function redirectToIndex has been removed to avoid duplication
