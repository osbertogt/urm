<?php
require_once '../../../assets/dbc.php';

$allowed = [
    'motivo_consulta',
    'diagnostico',
    'examen_fisico',
    'medicamentos_administrados',
	'sintomas',
	'indicaciones_medicas',
	'receta',
    'historia_enfermedad_actual'
];

$id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
$campo = $_POST['campo'] ?? '';

if (!$id_cliente || !in_array($campo, $allowed)) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT 
        a.fecha,
        a.numero_ticket,
        c.$campo AS valor
    FROM tbl_atencion_consulta c
    JOIN tbl_atencion a ON c.id_atencion = a.id
    WHERE a.id_cliente = ?
    AND c.$campo IS NOT NULL
    AND c.$campo <> ''
    ORDER BY a.fecha DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
