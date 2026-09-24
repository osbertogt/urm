<?php
require_once '../../assets/dbc.php';
$id_medico = $_GET['id_medico'] ?? null;
$fecha = $_GET['fecha'] ?? null;
$dia_semana = date('N', strtotime($fecha));

$sql = "SELECT h.id, h.hora, h.paciente_nombre
        FROM tbl_cita h
        WHERE h.id_medico = ? AND h.fecha = ? ORDER by h.hora ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_medico, $fecha);
$stmt->execute();
$result = $stmt->get_result();
$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = $row;
}
echo json_encode($options);
?>