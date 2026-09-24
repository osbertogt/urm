<?php
require_once '../../assets/dbc.php';

$sql = "SELECT id, nombre FROM cat_servicio ORDER BY nombre";
$result = $conn->query($sql);

$datos = [];
while ($row = $result->fetch_assoc()) {
    $datos[] = [
        'id' => $row['id'],
        'text' => $row['nombre'] 
    ];
}

header('Content-Type: application/json');
echo json_encode($datos);
?>
