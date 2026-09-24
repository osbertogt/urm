<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../../assets/dbc.php';

$id_departamento = isset($_GET['id_departamento']) ? intval($_GET['id_departamento']) : 0;

if ($id_departamento <= 0) {
    echo json_encode([]);
    exit;
}

$query = "SELECT id, nombre 
          FROM cat_municipio 
          WHERE id_departamento = ? 
          ORDER BY nombre ASC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(['error' => 'Error en la preparación de la consulta']);
    exit;
}

$stmt->bind_param("i", $id_departamento);
$stmt->execute();
$result = $stmt->get_result();

$municipios = [];
while ($row = $result->fetch_assoc()) {
    $municipios[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre']
    ];
}

$stmt->close();
echo json_encode($municipios);
exit;