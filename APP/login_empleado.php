<?php
include 'db_config.php';
$data = json_decode(file_get_contents('php://input'), true);
$user = $data['usuario'];
$pass = $data['contrasena'];

$stmt = $conn->prepare("SELECT * FROM empleado WHERE usuario = ? AND contrasena = ?");
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {
    echo json_encode(["ok" => true, "mensaje" => "Bienvenido", "empleado" => $user]);
} else {
    echo json_encode(["ok" => false, "mensaje" => "Credenciales incorrectas"]);
}
?>