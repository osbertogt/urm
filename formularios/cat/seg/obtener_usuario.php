<?php
require_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$stmt = $conn->prepare("SELECT id, nombre_usuario, nombres, apellidos, email, id_rol, activo FROM cat_usuario WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$user = $res->fetch_assoc();
echo json_encode(['success' => true, 'data' => $user]);
