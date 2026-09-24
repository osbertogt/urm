<?php
require_once '../../assets/dbc.php';
$data = [];
$res = mysqli_query($conn, "SELECT id, nombre FROM cat_servicio WHERE id_tipo_analisis<99 ORDER BY nombre");
while ($row = mysqli_fetch_assoc($res)) {
  $data[] = $row;
}
echo json_encode($data);
?>
