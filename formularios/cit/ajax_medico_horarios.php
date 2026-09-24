<?php
require_once '../../assets/dbc.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

if ($action === 'listar') {
  $sql = "SELECT h.*, CONCAT_WS(' ', p.nombre_1, p.nombre_2, p.apellido_1, p.apellido_2) AS medico
          FROM tbl_medico_horario_atencion_fecha h
          LEFT JOIN tbl_persona p ON p.id = h.id_medico
          ORDER BY h.id DESC";
  $res = mysqli_query($conn, $sql);
  $data = [];
  while ($row = mysqli_fetch_assoc($res)) $data[] = $row;
  echo json_encode($data);
  exit;
}

if ($action === 'obtener') {
  $id = intval($_POST['id'] ?? 0);
  $sql = "SELECT * FROM tbl_medico_horario_atencion_fecha WHERE id = $id";
  $res = mysqli_query($conn, $sql);
  if ($row = mysqli_fetch_assoc($res)) {
    echo json_encode(['success' => true, 'data' => $row]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
  }
  exit;
}

if ($action === 'guardar') {
  $id = intval($_POST['id'] ?? 0);
  $id_medico = intval($_POST['id_medico'] ?? 0);
  $fecha_inicio = mysqli_real_escape_string($conn, $_POST['fecha_inicio'] ?? '');
  $fecha_final = mysqli_real_escape_string($conn, $_POST['fecha_final'] ?? '');
  $habilitado = intval($_POST['habilitado'] ?? 1);

  if ($id_medico <= 0 || !$fecha_inicio || !$fecha_final) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
  }

  if ($id > 0) {
    $sql = "UPDATE tbl_medico_horario_atencion_fecha
            SET id_medico=$id_medico, fecha_inicio='$fecha_inicio', fecha_final='$fecha_final', habilitado=$habilitado
            WHERE id=$id";
  } else {
    $sql = "INSERT INTO tbl_medico_horario_atencion_fecha (id_medico, fecha_inicio, fecha_final, habilitado)
            VALUES ($id_medico, '$fecha_inicio', '$fecha_final', $habilitado)";
  }

  echo json_encode(['success' => mysqli_query($conn, $sql)]);
  exit;
}

if ($action === 'eliminar') {
  $id = intval($_POST['id'] ?? 0);
  $ok = mysqli_query($conn, "DELETE FROM tbl_medico_horario_atencion_fecha WHERE id=$id");
  echo json_encode(['success' => $ok]);
  exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no válida']);
