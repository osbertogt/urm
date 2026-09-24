<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

$sql = "SELECT id, nombre FROM cat_formapago ORDER BY id";
$result = $conn->query($sql);

$formas = [];
while ($row = $result->fetch_assoc()) {
    $formas[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre']
    ];
}

echo json_encode($formas);
?>
