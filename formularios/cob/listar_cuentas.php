<?php
require_once '../../assets/dbc.php';

header('Content-Type: application/json');

$sql = "SELECT 
    cc.id,
    cc.codigo,
    cc.fecha,
    cc.total,
    cc.abono + COALESCE((
        SELECT SUM(p.total)
        FROM tbl_cuenta_cliente_pago p
        WHERE p.id_cuenta = cc.id
    ), 0) AS abono_actual,
    (cc.saldo - COALESCE((
        SELECT SUM(p.total)
        FROM tbl_cuenta_cliente_pago p
        WHERE p.id_cuenta = cc.id
    ), 0)) AS saldo_actual,
    CONCAT_WS(' ', p.nombre_1, p.nombre_2, p.apellido_1, p.apellido_2) AS cliente
FROM tbl_cuenta_cliente cc
LEFT JOIN tbl_persona p ON p.id = cc.id_cliente
ORDER BY cc.id DESC";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'cliente' => $row['cliente'],
        'fecha' => $row['fecha'],
        'codigo' => $row['codigo'],
        'total' => number_format($row['total'], 2),
        'abono' => number_format($row['abono_actual'], 2),
        'saldo' => number_format($row['saldo_actual'], 2),
        'acciones' => '<button class="btn btn-sm btn-primary">Editar</button> <button class="btn btn-sm btn-danger">Eliminar</button>'
    ];
}

echo json_encode(['data' => $data]);
?>
