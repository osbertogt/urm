<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

// Validar entrada
if (!isset($_POST['id_cita'])) {
    echo json_encode(['success' => false, 'error' => 'Falta id_cita']);
    exit;
}

$id_cita = intval($_POST['id_cita']);

// 1. Obtener id_atencion
$sql1 = "SELECT id FROM tbl_atencion WHERE id_cita = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $id_cita);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result1->num_rows === 0) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$row1 = $result1->fetch_assoc();
$id_atencion = $row1['id'];

// 2. Obtener los signos
$sql2 = "SELECT 
            fecha_hora,
			sv_presion_arterial,
            sv_frecuencia_cardiaca,
            sv_frecuencia_respiratoria,
            sv_temperatura,
            sv_ausc_pulmonar
         FROM tbl_atencion_consulta
         WHERE id_atencion = ?
         LIMIT 1";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id_atencion);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows === 0) {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$data = $result2->fetch_assoc();

echo json_encode([
    'success' => true,
    'data' => $data
]);
