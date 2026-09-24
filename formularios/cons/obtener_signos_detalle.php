<?php
session_start();
require_once '../../assets/dbc.php';

// Verificar que se proporcionó un ID
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

// Consultar el registro específico
$query = "SELECT * FROM tbl_atencion_consulta_siv WHERE id = $id";
$result = $mysqli->query($query);

if ($result && $result->num_rows > 0) {
    $registro = $result->fetch_assoc();
    echo json_encode($registro);
} else {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
}

$mysqli->close();
?>