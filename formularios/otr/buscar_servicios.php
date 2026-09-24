<?php
require '../../assets/dbc.php';

$term = $_GET['term'] ?? '';
$id_tipo_servicio = $_GET['id_tipo_servicio'] ?? '';

$sql = "SELECT a.id, a.nombre, 0 precio 
        FROM cat_servicio a
        WHERE a.nombre LIKE ?";

$params = [];
$types  = "s";
$like   = "%$term%";
$params[] = &$like;

if (!empty($id_tipo_servicio)) {
    $sql .= " AND a.id_tipo_atencion = ?";
    $types .= "i";
    $params[] = &$id_tipo_servicio;
}

$sql .= " ORDER BY a.nombre";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
