<?php
require_once '../../assets/dbc.php';
$id_medico = $_GET['id_medico'] ?? null;
$fecha = $_GET['fecha'] ?? null;
$dia_semana = date('N', strtotime($fecha));

$sql = "SELECT h.id, h.hora
        FROM tbl_medico_horario_atencion h
        WHERE h.id_medico = ? AND h.dia_semana = ? AND NOT EXISTS (
            SELECT 1 FROM tbl_cita c WHERE c.id_horario = h.id AND c.fecha = ? AND c.id_medico = ?
        )";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisi", $id_medico, $dia_semana, $fecha, $id_medico);
$stmt->execute();
$result = $stmt->get_result();
$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = $row;
}
echo json_encode($options);
?>