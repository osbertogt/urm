<?php
// listar_consultas.php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

// Retornar lista de consultas (tbl_atencion_consulta JOIN tbl_atencion + personas + cat_motivo_admision + cat_especialidad)
$sql = "
SELECT
  c.id AS id,
  DATE_FORMAT(a.fecha, '%Y-%m-%d %H:%i:%s') AS fecha,
  a.numero_ticket,
  COALESCE(e.nombre, '') AS especialidad,
  COALESCE(m.nombre, '') AS motivo,
  TRIM(CONCAT_WS(' ', cli.nombre_1, cli.nombre_2, cli.apellido_1, cli.apellido_2)) AS cliente,
  TRIM(CONCAT_WS(' ', med.nombre_1, med.nombre_2, med.apellido_1, med.apellido_2)) AS medico
FROM tbl_atencion_consulta c
JOIN tbl_atencion a ON a.id = c.id_atencion
LEFT JOIN tbl_persona cli ON a.id_cliente = cli.id
LEFT JOIN tbl_persona med ON a.id_medico = med.id
LEFT JOIN cat_motivo_admision m ON c.id_tipo_atencion = m.id
LEFT JOIN cat_especialidad e ON med.id_especialidad = e.id
ORDER BY a.fecha DESC
";

$res = $conn->query($sql);
$out = [];

if ($res) {
    while ($r = $res->fetch_assoc()) {
        // sanea para la tabla
        $out[] = [
            'id' => (int)$r['id'],
            'fecha' => htmlspecialchars($r['fecha']),
            'numero_ticket' => htmlspecialchars($r['numero_ticket']),
            'especialidad' => htmlspecialchars($r['especialidad']),
            'motivo' => htmlspecialchars($r['motivo']),
            'cliente' => htmlspecialchars($r['cliente']),
            'medico' => htmlspecialchars($r['medico']),
        ];
    }
}

echo json_encode($out, JSON_UNESCAPED_UNICODE);
