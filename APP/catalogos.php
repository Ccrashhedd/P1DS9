<?php
// catalogos.php
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "ds9p1"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["ok" => false, "mensaje" => "Error de conexión: " . $conn->connect_error]));
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'getCategorias') {
        // Usamos el nombre de la tabla 'categoria' en minúsculas
        $sql = "SELECT idCategoria, nombreCat FROM categoria";
        $result = $conn->query($sql);
        $data = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $data[] = ["idCategoria" => (int)$row['idCategoria'], "nombreCat" => $row['nombreCat']];
            }
        }
        echo json_encode($data);
    } elseif ($action === 'getMarcas') {
        // Usamos el nombre de la tabla 'marca' en minúsculas
        $sql = "SELECT idMarca, nombreMarc FROM marca";
        $result = $conn->query($sql);
        $data = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $data[] = ["idMarca" => (int)$row['idMarca'], "nombreMarc" => $row['nombreMarc']];
            }
        }
        echo json_encode($data);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    
    if (empty($nombre)) {
        echo json_encode(["ok" => false, "mensaje" => "Nombre vacío"]);
        exit;
    }

    if ($action === 'addCategoria') {
        // Ajustado a tabla 'categoria'
        $stmt = $conn->prepare("INSERT INTO categoria (nombreCat) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        if ($stmt->execute()) {
            echo json_encode(["ok" => true, "mensaje" => "Categoría guardada"]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => $conn->error]);
        }
        $stmt->close();
    } elseif ($action === 'addMarca') {
        // Ajustado a tabla 'marca'
        $stmt = $conn->prepare("INSERT INTO marca (nombreMarc) VALUES (?)");
        $stmt->bind_param("s", $nombre);
        if ($stmt->execute()) {
            echo json_encode(["ok" => true, "mensaje" => "Marca guardada"]);
        } else {
            echo json_encode(["ok" => false, "mensaje" => $conn->error]);
        }
        $stmt->close();
    }
}
$conn->close();
?>