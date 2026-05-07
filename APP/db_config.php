<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "ds9p1";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // En lugar de die con texto, enviamos un JSON de error
    header('Content-Type: application/json');
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

// IMPORTANTE: Para que json_encode no devuelva null con tildes o eñes
$conn->set_charset("utf8");
?>