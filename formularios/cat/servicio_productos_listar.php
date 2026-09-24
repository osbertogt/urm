<?php
require_once '../../assets/dbc.php';

$id_servicio = intval($_GET['id_servicio']);
$sql = "SELECT ps.id, p.nombre AS nombre, ps.cantidad 
        FROM tbl_productos_servicios ps
        JOIN cat_producto p ON ps.id_producto = p.id
        WHERE ps.id_servicio = $id_servicio";
$res = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
  $data[] = $row;
}
echo json_encode($data);
