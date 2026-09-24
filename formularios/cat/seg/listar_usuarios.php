<?php
require_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT u.id, u.nombre_usuario, u.nombres, u.apellidos, u.email, u.activo, r.nombre as rol 
        FROM cat_usuario u 
        LEFT JOIN cat_rol r ON u.id_rol = r.id
        ORDER BY u.id DESC";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
