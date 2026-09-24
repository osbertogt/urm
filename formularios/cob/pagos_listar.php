<?php
require '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo json_encode([]); exit; }

$sql = "SELECT id, fecha, total, notas FROM tbl_cuenta_cliente_pago WHERE id_cuenta = ? ORDER BY fecha DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$out = [];
while ($r = mysqli_fetch_assoc($res)) $out[] = $r;

echo json_encode($out, JSON_UNESCAPED_UNICODE);
mysqli_stmt_close($stmt);
mysqli_close($conn);
