<?php
require_once '../../assets/dbc.php';
$id_paquete = intval($_GET['id_paquete']);
$data = [];

$sql = "SELECT ps.id, s.nombre AS servicio
        FROM cat_paquete_servicio ps
        JOIN cat_servicio s ON ps.id_servicio = s.id
        WHERE ps.id_paquete = $id_paquete";
$res = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($res)) {
  $data[] = $row;
}
echo json_encode(["data" => $data]);
?>
