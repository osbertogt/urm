<?php
require_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');
$sql = "SELECT id, nombre FROM cat_rol ORDER BY id DESC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['data' => [], 'error' => 'Error en la preparación de la consulta']);
    exit;
}
$stmt->execute();
$result = $stmt->get_result();
$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = [
        'id'     => (int)$row['id'],
        'nombre' => $row['nombre']
    ];
}
$stmt->close();
$conn->close();
echo json_encode(['data' => $roles], JSON_UNESCAPED_UNICODE);
