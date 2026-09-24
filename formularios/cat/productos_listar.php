<?php
require_once '../../assets/dbc.php';

$sql = "SELECT id, nombre FROM cat_producto ORDER BY nombre";
$result = $conn->query($sql);

$datos = [];
while ($row = $result->fetch_assoc()) {
    $datos[] = [
        'id' => $row['id'],
        'text' => $row['nombre'] // ✅ clave requerida
    ];
}

header('Content-Type: application/json');
echo json_encode($datos);
?>
