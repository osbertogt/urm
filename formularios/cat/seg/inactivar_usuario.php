<?php
require_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$stmt = $conn->prepare("UPDATE cat_usuario SET activo = 1 - activo WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado']);
}
