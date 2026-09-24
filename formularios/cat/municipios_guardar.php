<?php
include '../../dbc.php';

$id = $_POST['id'] ?? '';
$nombre = $_POST['nombre'];
$id_departamento = $_POST['id_departamento'];

if ($id == '') {
  $sql = "INSERT INTO cat_municipio (nombre, id_departamento) VALUES (?, ?)";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'si', $nombre, $id_departamento);
} else {
  $sql = "UPDATE cat_municipio SET nombre=?, id_departamento=? WHERE id=?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'sii', $nombre, $id_departamento, $id);
}

if (mysqli_stmt_execute($stmt)) {
  echo json_encode(['status' => 'ok']);
} else {
  echo json_encode(['status' => 'error', 'mensaje' => mysqli_error($conn)]);
}
?>
