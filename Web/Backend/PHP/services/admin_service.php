<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

/**
 * Agrega una nueva categoría
 */
function agregarCategoria(string $nombre): int
{
	$nombre = trim($nombre);
	if ($nombre === '') {
		return 0;
	}

	try {
		$stmt = db()->prepare('INSERT INTO categoria (nombreCat) VALUES (?)');
		$stmt->execute([$nombre]);
		return (int) db()->lastInsertId();
	} catch (Throwable $e) {
		return 0;
	}
}

/**
 * Actualiza una categoría existente
 */
function actualizarCategoria(int $idCategoria, string $nombre): bool
{
	$nombre = trim($nombre);
	if ($idCategoria <= 0 || $nombre === '') {
		return false;
	}

	try {
		$stmt = db()->prepare('UPDATE categoria SET nombreCat = ? WHERE idCategoria = ?');
		return $stmt->execute([$nombre, $idCategoria]);
	} catch (Throwable $e) {
		return false;
	}
}

/**
 * Agrega una nueva marca
 */
function agregarMarca(string $nombre): int
{
	$nombre = trim($nombre);
	if ($nombre === '') {
		return 0;
	}

	try {
		$stmt = db()->prepare('INSERT INTO marca (nombreMarc) VALUES (?)');
		$stmt->execute([$nombre]);
		return (int) db()->lastInsertId();
	} catch (Throwable $e) {
		return 0;
	}
}

/**
 * Actualiza una marca existente
 */
function actualizarMarca(int $idMarca, string $nombre): bool
{
	$nombre = trim($nombre);
	if ($idMarca <= 0 || $nombre === '') {
		return false;
	}

	try {
		$stmt = db()->prepare('UPDATE marca SET nombreMarc = ? WHERE idMarca = ?');
		return $stmt->execute([$nombre, $idMarca]);
	} catch (Throwable $e) {
		return false;
	}
}

?>

?>