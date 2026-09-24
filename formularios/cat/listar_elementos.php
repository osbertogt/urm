<?php
if (isset($_GET['accion']) && $_GET['accion'] === 'elementos') {
  require_once '../../assets/dbc.php';
  $tipo = $_GET['tipo'];
  $tabla = '';

  switch ($tipo) {
    case 'especialidad': $tabla = 'cat_especialidad'; break;
    case 'servicios': $tabla = 'cat_servicio WHERE id_tipo_atencion>1'; break;
    case 'analisis': $tabla = 'cat_servicio WHERE id_tipo_atencion=1'; break;
    default: exit(json_encode([]));
  }

  $sql = "SELECT id, nombre FROM $tabla ORDER BY nombre";
  $res = $conn->query($sql);
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
  echo json_encode($data);
  exit;
}
?>
