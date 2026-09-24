<?php
session_start();
require_once '../../assets/dbc.php';

// Verificar que se haya proporcionado el ID del cliente
if (!isset($_GET['id_cliente'])) {
    echo json_encode(['error' => 'ID de cliente no proporcionado']);
    exit;
}

$id_cliente = intval($_GET['id_cliente']);

// Consultar signos vitales del cliente
$query = "SELECT * FROM tbl_atencion_consulta_siv 
          WHERE id_cliente = $id_cliente 
          ORDER BY fecha DESC";
$result = $conn->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$conn->close();
?>