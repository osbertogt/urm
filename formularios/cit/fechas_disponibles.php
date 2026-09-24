<?php
require_once '../../config/db.php'; 

header('Content-Type: application/json; charset=utf-8');

$id_medico = intval($_GET['id_medico'] ?? 0);

if ($id_medico <= 0) {
  echo json_encode([]);
  exit;
}


$sql = "SELECT DATE(fecha_inicio) AS fecha_inicio, habilitado 
        FROM tbl_medico_horario_atencion_fecha
        WHERE id_medico = ?";
		
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_medico);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$fechas = [];
while ($row = mysqli_fetch_assoc($result)) {
  $fechas[] = $row;
}

mysqli_close($conn);

echo json_encode($fechas);
