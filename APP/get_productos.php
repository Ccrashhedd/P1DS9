<?php
include 'db_config.php';
header('Content-Type: application/json');

// Eliminamos el WHERE stock > 0 para enviar el catálogo completo
$result = $conn->query("SELECT * FROM productos");

if ($result) {
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
} else {
    echo json_encode([]);
}
?>