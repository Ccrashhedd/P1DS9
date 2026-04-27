<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Constantes de configuración de base de datos
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'ds9p1';

/**
 * Obtiene la conexión PDO singleton a la base de datos.
 * 
 * @return PDO Instancia de conexión a la base de datos
 * @throws PDOException Si la conexión falla
 */
function db(): PDO
{
    static $conexion = null;

    if ($conexion instanceof PDO) {
        return $conexion;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $conexion = new PDO($dsn, DB_USER, DB_PASS, $options);
        
    } catch (PDOException $e) {
        // En desarrollo, mostrar error; en producción, loggear
        throw new PDOException("Error de conexión a la base de datos: " . $e->getMessage());
    }

    return $conexion;
}

/**
 * Obtiene la URL de la aplicación actual (sin query string).
 * 
 * @return string URL de la aplicación
 */
function appUrl(): string
{
    return strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?') ?: '/index.php';
}

/**
 * Redirige a la página de inicio sin retorno.
 * 
 * @return never
 */
function redirectToIndex(): never
{
    header('Location: ' . appUrl());
    exit;
}
