<?php
include 'db_config.php';
$result = $conn->query("SELECT * FROM productos WHERE stock > 0");
echo json_encode($result->fetch_all(MYSQLI_ASSOC));
?>