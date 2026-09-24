<?php
require '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success' => false]); exit; }

$sql = "SELECT id, fecha, total, notas FROM tbl_cuenta_cliente_pago WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);

if ($data) {
  echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} else {
  echo json_encode(['success' => false, 'message' => 'No encontrado']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
