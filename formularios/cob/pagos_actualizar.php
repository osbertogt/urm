<?php
require '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id_pago'] ?? 0);
$total = floatval($_POST['total'] ?? 0);
$notas = trim($_POST['notas'] ?? '');

if ($id <= 0 || $total <= 0) {
  echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
  exit;
}

$sql = "UPDATE tbl_cuenta_cliente_pago SET total = ?, notas = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'dsi', $total, $notas, $id);

if (mysqli_stmt_execute($stmt)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
