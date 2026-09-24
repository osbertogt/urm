<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT id, 
        CONCAT_WS(' ', nombre_1, ' ', nombre_2, ' ', apellido_1, ' ', apellido_2) AS nombre 
        FROM tbl_persona 
        WHERE id_tipopersona = 3
		ORDER BY apellido_1, nombre_1";

$result = $conn->query($sql);
$medico = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $medico[] = $row;
    }
}

echo json_encode($medico);
