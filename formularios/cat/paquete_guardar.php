<?php
require_once '../../assets/dbc.php';
$id = isset($_POST['id_paquete']) ? intval($_POST['id_paquete']) : 0;
$nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
$precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;

if ($id) {
    // Actualizar
    $sql = "UPDATE cat_paquete SET nombre=?, precio=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $nombre, $precio, $id);
} else {
    // Insertar
    $sql = "INSERT INTO cat_paquete (nombre, precio) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sd", $nombre, $precio);
}
$stmt->execute();
$stmt->close();
echo "ok";
?>
