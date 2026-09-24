<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id_medico = $_GET['filtroMedico'] ?? null;
$id_estado = $_GET['filtroEstado'] ?? null;

$sql = "SELECT c.id, c.fecha, c.hora, c.paciente_nombre,
               CONCAT_WS(' ', p.nombre_1, p.apellido_1) AS medico, c.id_estado
        FROM tbl_cita c
        JOIN tbl_persona p ON c.id_medico = p.id
        WHERE 1=1";

$params = []; $types = '';

if ($id_medico) { $sql .= " AND c.id_medico = ?"; $params[] = $id_medico; $types .= 'i'; }
if ($id_estado) { $sql .= " AND c.id_estado = ?"; $params[] = $id_estado; $types .= 'i'; }

$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $eventos = [];
    while ($row = $result->fetch_assoc()) {
        $eventos[] = [
            'id' => $row['id'],
            'title' => $row['paciente_nombre'] . ' con ' . $row['medico'],
            'start' => $row['fecha'] . ($row['hora'] ? 'T' . $row['hora'] : ''),
            'extendedProps' => ['medico' => $row['medico'], 'estado' => $row['id_estado']],
            'color' => $row['id_estado'] == 1 ? '#198754' : ($row['id_estado'] == 2 ? '#dc3545' : '#ffc107')
        ];
    }
    
    echo json_encode($eventos);
    $stmt->close();
} else {
    echo json_encode(['error' => 'Error en consulta']);
}
$conn->close();
?>
