<?php
include '../../dbc.php';
$tabla = $_POST['tabla'];
$id = $_POST['id'];
$nombre = trim($_POST['nombre']);
var_dump($_POST);
exit;
if ($id == '') {
  $stmt = $conn->prepare("INSERT INTO $tabla (nombre) VALUES (?)");
  $stmt->bind_param("s", $nombre);
} else {
  $stmt = $conn->prepare("UPDATE $tabla SET nombre = ? WHERE id = ?");
  $stmt->bind_param("si", $nombre, $id);
}
$stmt->execute();
echo "OK";
?>