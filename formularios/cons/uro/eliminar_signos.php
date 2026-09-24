<?php
session_start();
require_once '../../assets/dbc.php';

// Verificar que se proporcionó un ID
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = intval($_POST['id']);
$id_cliente = $_SESSION['cliente_activo'];

// Verificar que el registro pertenece al cliente antes de eliminar
$check_query = "SELECT id FROM tbl_atencion_consulta_siv 
                WHERE id = $id AND id_cliente = $id_cliente";
$check_result = $mysqli->query($check_query);

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado o no pertenece al cliente']);
    exit;
}

// Eliminar el registro
$query = "DELETE FROM tbl_atencion_consulta_siv 
          WHERE id = $id AND id_cliente = $id_cliente";

if ($mysqli->query($query)) {
    echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $mysqli->error]);
}

$mysqli->close();
?>