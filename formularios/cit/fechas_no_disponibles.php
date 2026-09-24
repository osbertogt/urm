<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT fecha_inicio, fecha_final FROM tbl_medico_horario_atencion_fecha WHERE habilitado = 0";
$res = $conn->query($sql);

$rangos = [];
if ($res && $res->num_rows > 0) {
  while ($row = $res->fetch_assoc()) {
    $rangos[] = [
      'fecha_inicio' => $row['fecha_inicio'],
      'fecha_final' => $row['fecha_final']
    ];
  }
}

echo json_encode($rangos);
$conn->close();
