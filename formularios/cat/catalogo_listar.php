<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');
$tabla = $_POST['tabla'];
$sql = "SELECT id, nombre FROM $tabla ORDER BY nombre";
$res = mysqli_query($conn, $sql);
$data = [];
while ($row = mysqli_fetch_assoc($res)) {
  $data[] = $row;
}
echo json_encode(['data' => $data]);
