<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once '../../assets/dbc.php';

  $id = $_POST['id'] ?? null;
  $tipo = $_POST['tipo_servicio'];
  $id_elemento = $_POST['elemento'] ?? null;
  $id_tipo_precio = $_POST['id_tipo_precio'] ?? null;
  $precio = $_POST['precio'];
  $fecha = date('Y-m-d');

  $id_especialidad = $id_servicio = $id_paquete = null;
  if ($tipo === 'especialidad') $id_especialidad = $id_elemento;
  if ($tipo === 'analisis') $id_servicio = $id_elemento;
  if ($tipo === 'servicios') $id_servicio = $id_elemento;

  if ($id) {
    $stmt = $conn->prepare("UPDATE tbl_precio_servicio SET id_especialidad=?, id_servicio=?, id_paquete=?, id_tipo_precio=?, precio=?, fecha_ultimo_precio=? WHERE id=?");
    $stmt->bind_param("iiiidsi", $id_especialidad, $id_servicio, $id_paquete, $id_tipo_precio, $precio, $fecha, $id);
  } else {
    $stmt = $conn->prepare("INSERT INTO tbl_precio_servicio (id_especialidad, id_servicio, id_paquete, id_tipo_precio, precio, fecha_ultimo_precio) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiids", $id_especialidad, $id_servicio, $id_paquete, $id_tipo_precio, $precio, $fecha);
  }

  $stmt->execute();
  echo "ok";
  exit;
}
?>