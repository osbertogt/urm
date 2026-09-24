<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

if ($action === 'verificar_cliente') {
    $id_cita = intval($_POST['id_cita']);
    $stmt = $conn->prepare("SELECT id_cliente FROM tbl_cita WHERE id = ?");
    $stmt->bind_param("i", $id_cita);
    $stmt->execute();
    $stmt->bind_result($id_cliente);
    $stmt->fetch();
    $stmt->close();
    echo json_encode(['tiene_cliente' => !empty($id_cliente)]);
    exit;
}

if ($action === 'asignar_cliente') {
    $id_cita = intval($_POST['id_cita']);
    $id_cliente = intval($_POST['id_cliente']);

    $stmt = $conn->prepare("UPDATE tbl_cita SET id_cliente = ? WHERE id = ?");
    $stmt->bind_param("ii", $id_cliente, $id_cita);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'Cliente asignado correctamente' : 'Error al asignar el cliente'
    ]);
    exit;
}
?>
