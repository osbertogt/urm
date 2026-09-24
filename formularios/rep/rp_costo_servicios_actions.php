<?php
if (!isset($_SESSION)) { session_start(); }
require '../../assets/dbc.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';

if ($action === 'listar') {
  $tipo = $_POST['tiposervicio'] ?? '';
  $fechaInicio = $_POST['fechaInicio'] ?? '';
  $fechaFin = $_POST['fechaFin'] ?? '';

  $sql = "SELECT fecha, tiposervicio, numero_ticket, paciente, dpi, servicio, costo 
          FROM vw_servicios_paciente WHERE 1=1";
  $params = [];
  $types = '';

  if (!empty($tipo)) {
    $sql .= " AND tiposervicio = ?";
    $types .= 's';
    $params[] = $tipo;
  }

  if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sql .= " AND fecha BETWEEN ? AND ?";
    $types .= 'ss';
    $params[] = $fechaInicio;
    $params[] = $fechaFin;
  }

  $sql .= " ORDER BY fecha DESC, tiposervicio ASC";

  $stmt = $conn->prepare($sql);

  if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
  }

  $stmt->execute();
  $result = $stmt->get_result();

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode($data);
  $stmt->close();
  $conn->close();
}
?>
