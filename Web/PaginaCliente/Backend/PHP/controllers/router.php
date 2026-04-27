<?php

/**
 * WRAPPER DE COMPATIBILIDAD TEMPORAL
 * 
 * Este archivo ha sido consolidado en Web/Backend/PHP/controllers/router.php
 * y migrado de mysqli a PDO.
 * 
 * Este wrapper mantendrá compatibilidad por un período de transición.
 * Para nuevos desarrollos, importar directamente de:
 *   require_once __DIR__ . '/../../Backend/PHP/controllers/router.php';
 */

require_once dirname(__DIR__, 4) . '/Backend/PHP/controllers/router.php';


