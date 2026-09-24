<?php
require '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id_cuenta = intval($_POST['id_cuenta'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$id1 = intval($_POST['id_forma_pago1'] ?? 0);
$id2 = intval($_POST['id_forma_pago2'] ?? 0);
$id3 = intval($_POST['id_forma_pago3'] ?? 0);
$m1 = floatval($_POST['monto_1'] ?? 0);
$m2 = floatval($_POST['monto_2'] ?? 0);
$m3 = floatval($_POST['monto_3'] ?? 0);
$total = floatval($_POST['total'] ?? 0);
$notas = trim($_POST['notas'] ?? '');

if ($id_cuenta <= 0 || $total <= 0) {
  echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
  exit;
}

$sql = "INSERT INTO tbl_cuenta_cliente_pago 
(id_cuenta, fecha, id_forma_pago1, id_forma_pago2, id_forma_pago3, monto_1, monto_2, monto_3, total, notas)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'isiiidddds', 
  $id_cuenta, $fecha, $id1, $id2, $id3, $m1, $m2, $m3, $total, $notas
);

if (mysqli_stmt_execute($stmt)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
