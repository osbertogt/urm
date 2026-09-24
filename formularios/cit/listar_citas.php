<?php
// listar_citas.php
require_once '../../assets/dbc.php';

$sql = "SELECT c.id,
               CONCAT_WS(' ', p.nombre_1, p.nombre_2, p.apellido_1, p.apellido_2) AS medico,
               e.nombre AS especialidad,
               c.fecha,
			   c.hora,
               c.codigo,
               c.paciente_nombre,
               c.paciente_telefono,
               c.paciente_email,
               s.nombre AS estado
        FROM tbl_cita c
        JOIN tbl_persona p ON c.id_medico = p.id
        JOIN cat_especialidad e ON p.id_especialidad = e.id
        JOIN cat_estado s ON c.id_estado = s.id";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data]);
?>
