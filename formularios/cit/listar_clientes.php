<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT id, 
        CONCAT_WS(' ', nombre_1, ' ', nombre_2, ' ', apellido_1, ' ', apellido_2) AS nombre_completo 
        FROM tbl_persona 
        WHERE id_tipopersona = 1";

if ($search !== '') {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (nombre_1 LIKE '%$search%' 
              OR nombre_2 LIKE '%$search%' 
              OR apellido_1 LIKE '%$search%' 
              OR apellido_2 LIKE '%$search%')";
}

$sql .= " ORDER BY apellido_1, nombre_1 LIMIT 20";

$result = $conn->query($sql);
$clientes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}

echo json_encode($clientes);
