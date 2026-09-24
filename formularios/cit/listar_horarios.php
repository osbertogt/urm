<?php
// listar_horarios.php
require_once '../../assets/dbc.php';

$sql = "SELECT h.id, 
               CONCAT_WS(' ', p.nombre_1, p.nombre_2, p.apellido_1, p.apellido_2) AS medico,
               e.nombre AS especialidad,
               h.dia_semana,
               CONCAT(h.hora,'-',h.hora2) hora
        FROM tbl_medico_horario_atencion h
        JOIN tbl_persona p ON h.id_medico = p.id
        JOIN cat_especialidad e ON p.id_especialidad = e.id
		ORDER BY FIELD(h.dia_semana, 1, 2, 3, 4, 5, 6, 0)";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data]);
