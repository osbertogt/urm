<?php
include '../../dbc.php';
$sql = "SELECT m.id, m.nombre, d.nombre AS departamento
        FROM cat_municipio m
        JOIN cat_departamento d ON m.id_departamento = d.id";
$res = mysqli_query($conn, $sql);
$data = [];
while ($row = mysqli_fetch_assoc($res)) {
  $data[] = $row;
}
echo json_encode(["data" => $data]);
