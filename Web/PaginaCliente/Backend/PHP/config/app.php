<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'ds9p1';
const DB_PORT = 3306;

function db(): mysqli
{
    static $conexion = null;

    if ($conexion instanceof mysqli) {
        return $conexion;
    }

    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conexion->set_charset('utf8mb4');

    return $conexion;
}

function appUrl(): string
{
    return strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?') ?: '/index.php';
}

function redirectToIndex(): never
{
    header('Location: ' . appUrl());
    exit;
}
