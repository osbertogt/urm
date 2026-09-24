<?php
include_once '../../assets/dbc.php';
header('Content-Type: application/json');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$query = "SELECT id, id_atencion, fecha, medicamentos, observaciones 
          FROM tbl_atencion_consulta_med 
          WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Registro no encontrado']);
}

$conn->close();
?>