<?php
if (isset($_GET['accion']) && $_GET['accion'] === 'listar') {
  require_once '../../assets/dbc.php';

  $sql = "SELECT ps.id,
       CONCAT(p.nombre, ' ', f.nombre, ', ', pr.nombre, ' ', um.nombre, ' ', p.concentracion, ' - Lote: ', mp.numero_lote) AS producto,
       tp.nombre AS tipo_precio,
       ps.precio,
       ps.fecha_ultimo_precio
FROM tbl_precio_servicio ps
JOIN tbl_inv_movimiento_producto mp ON ps.id_producto = mp.id
JOIN cat_producto p ON mp.id_producto = p.id
JOIN cat_fabricante f ON mp.id_fabricante = f.id
JOIN cat_unidad_medida um ON p.id_unidad_medida = um.id
JOIN cat_presentacion pr ON p.id_presentacion = pr.id
LEFT JOIN cat_tipo_precio tp ON ps.id_tipo_precio = tp.id;";

  $result = $conn->query($sql);
  $data = [];

  while ($row = $result->fetch_assoc()) {
    $row['precio'] = 'Q' . number_format($row['precio'], 2);
    $data[] = $row;
  }

  header('Content-Type: application/json');
  echo json_encode(['data' => $data]);
  exit;
}
?>