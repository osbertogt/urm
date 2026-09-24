<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once '../../assets/dbc.php';

  $id = $_POST['id'] ?? null;
  $id_producto = $_POST['id_producto'];
  $id_tipo_precio = $_POST['id_tipo_precio'];
  $precio = $_POST['precio'];
  $fecha = date('Y-m-d');

  if ($id) {
    $stmt = $conn->prepare("UPDATE tbl_precio_servicio SET id_producto=?, id_tipo_precio=?, precio=?, fecha_ultimo_precio=? WHERE id=?");
    $stmt->bind_param("iidsi", $id_producto, $id_tipo_precio, $precio, $fecha, $id);
  } else {
    $stmt = $conn->prepare("INSERT INTO tbl_precio_servicio (id_producto, id_tipo_precio, precio, fecha_ultimo_precio) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $id_producto, $id_tipo_precio, $precio, $fecha);
  }

  $stmt->execute();
  echo "ok";
  exit;
}
?>