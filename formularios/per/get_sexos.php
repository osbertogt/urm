<?php
require_once '../../assets/dbc.php';

header('Content-Type: application/json');

$sql = "SELECT id, nombre FROM cat_sexo ORDER BY nombre";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'text' => $row['nombre']
    ];
}

echo json_encode(['results' => $data]);
?>