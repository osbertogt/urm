<?php
if (isset($_GET['accion']) && $_GET['accion'] === 'listar') {
  require_once '../../assets/dbc.php';

  $sql = "SELECT ps.id,
                 CASE 
                    WHEN ps.id_especialidad IS NOT NULL THEN 'Especialidad'
                    WHEN ps.id_servicio IS NOT NULL THEN 'Servicio'
                    WHEN ps.id_paquete IS NOT NULL THEN 'Paquete'
                 END AS tipo,
                 COALESCE(e.nombre, s.nombre, pa.nombre) AS nombre,
                 tp.nombre AS tipo_precio,
                 ps.precio,
                 ps.fecha_ultimo_precio
          FROM tbl_precio_servicio ps
          LEFT JOIN cat_especialidad e ON ps.id_especialidad = e.id
          LEFT JOIN cat_servicio s ON ps.id_servicio = s.id
          LEFT JOIN cat_paquete pa ON ps.id_paquete = pa.id
          LEFT JOIN cat_tipo_precio tp ON ps.id_tipo_precio = tp.id";

  $result = $conn->query($sql);
  $data = [];

  while ($row = $result->fetch_assoc()) {
      $data[] = $row;
  }
  header('Content-Type: application/json');
//  echo json_encode(['data' => $data]);
echo json_encode(['data' => $data], JSON_PRETTY_PRINT);
exit;
}
?>
