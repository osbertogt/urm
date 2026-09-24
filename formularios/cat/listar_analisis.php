<?php
require '../../assets/dbc.php';
header('Content-Type: application/json');

$sql = "SELECT a.id, a.nombre, t.nombre AS tipo_atencion, COALESCE(costo,0) costo
        FROM cat_servicio a
        JOIN cat_tipo_atencion t ON a.id_tipo_atencion = t.id
		ORDER BY a.nombre";

$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode(['data' => $data]);
