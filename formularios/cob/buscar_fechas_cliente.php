<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

$id_cliente = $_GET['id_cliente'];

$query = "SELECT DISTINCT DATE(fecha) AS fecha_atencion 
          FROM tbl_atencion
          WHERE id_cliente=? 
          ORDER BY fecha_atencion DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['fecha_atencion'],
        'text' => $row['fecha_atencion']
    ];
}

echo json_encode(['data' => $data]);
