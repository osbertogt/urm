<?php
require_once '../../assets/dbc.php';

$sql = "SELECT s.id, s.nombre AS nombre_servicio, s.id_tipo_analisis, t.nombre AS nombre_tipo
        FROM cat_servicio s
        JOIN cat_tipo_analisis t ON t.id = s.id_tipo_analisis
        ORDER BY s.id DESC";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "data" => $data
]);
?>