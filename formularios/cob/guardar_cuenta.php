<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

$id_cliente     = $_POST['id_cliente'] ?? null;
$fecha          = $_POST['fecha'] ?? null;
$id_tipo_precio = $_POST['id_tipo_precio'] ?? null;
$total          = $_POST['total'] ?? 0;
$numero_pagos   = $_POST['numero_pagos'] ?? 1;
$monto_pago     = $_POST['monto_pago'] ?? 0;
$abono          = $_POST['abono'] ?? 0;
$saldo          = $_POST['saldo'] ?? 0;
$codigo         = $_POST['codigo'] ?? '';
$id_tipo_pago1  = $_POST['selectFormaPago1'] ?? null;
$id_tipo_pago2  = $_POST['id_tipo_pago2'] ?? null;
$id_tipo_pago3  = $_POST['id_tipo_pago3'] ?? null;
$monto1         = $_POST['monto1'] ?? 0;
$monto2         = $_POST['monto2'] ?? 0;
$monto3         = $_POST['monto3'] ?? 0;

$query = "INSERT INTO tbl_cuenta_cliente 
    (id_cliente, fecha, codigo, id_tipo_precio, id_tipo_pago1, id_tipo_pago2, id_tipo_pago3, 
	monto_1,monto_2,monto_3,total, numero_pagos, monto_pago, abono, saldo) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param(
    'issiiiidddddddd',
    $id_cliente,
    $fecha,
    $codigo,
    $id_tipo_precio,
    $id_tipo_pago1,
    $id_tipo_pago2,
    $id_tipo_pago3,
	$monto1,
	$monto2,
	$monto3,
    $total,
    $numero_pagos,
    $monto_pago,
    $abono,
    $saldo
);

$success = $stmt->execute();

if ($success) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
