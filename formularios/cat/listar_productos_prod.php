<?php
if (isset($_GET['accion']) && $_GET['accion'] === 'productos') {
  require_once '../../assets/dbc.php';

  $sql = "SELECT mp.id,
                 CONCAT(p.nombre, ' ', mp.nombre_comercial,', ', fa.nombre, ', ',pr.nombre, ' ', um.nombre, ' ', p.concentracion, ' - Lote: ', mp.numero_lote) AS nombre
          FROM tbl_inv_movimiento_producto mp
          JOIN cat_producto p ON mp.id_producto = p.id
          JOIN cat_fabricante fa ON mp.id_fabricante = fa.id
          JOIN cat_unidad_medida um ON p.id_unidad_medida = um.id
          JOIN cat_presentacion pr ON p.id_presentacion = pr.id
          ORDER BY nombre";

  $res = $conn->query($sql);
  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
  echo json_encode($data);
  exit;
}
?>