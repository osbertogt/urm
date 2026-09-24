<?php
include_once '../../assets/dbc.php';
header('Content-Type: application/json');

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$query = "DELETE FROM tbl_atencion_consulta_med WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
$conn->close();
?>