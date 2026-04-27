<?php

/**
 * WRAPPER DE COMPATIBILIDAD TEMPORAL
 * 
 * Este archivo ha sido consolidado en Web/Backend/PHP/config/app.php
 * y migrado de mysqli a PDO.
 * 
 * Este wrapper mantendrá compatibilidad por un período de transición.
 * Para nuevos desarrollos, importar directamente de:
 *   require_once __DIR__ . '/../../Backend/PHP/config/app.php';
 */

require_once dirname(__DIR__, 4) . '/Backend/PHP/config/app.php';

