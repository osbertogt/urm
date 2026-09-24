<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

$sql = "SELECT id, nombre FROM cat_tipo_precio ORDER BY nombre";
$result = $conn->query($sql);

$tipos = [];
while ($row = $result->fetch_assoc()) {
    $tipos[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre']
    ];
}

echo json_encode($tipos);
?>
