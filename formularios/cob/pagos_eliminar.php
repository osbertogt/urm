<?php
require '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'ID inválido']); exit; }

$sql = "DELETE FROM tbl_cuenta_cliente_pago WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
