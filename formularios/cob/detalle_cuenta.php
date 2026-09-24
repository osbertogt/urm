<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

$id_cliente = $_GET['id_cliente'] ?? 0;
$fecha = $_GET['fecha'] ?? '';
$id_tipo_precio = $_GET['id_tipo_precio'] ?? 0;
$id_forma_pago=$_GET['id_forma_pago'] ?? 0;
$total_general = $recargo = 0;
$data = [];
$totales = [];
$id_forma_pago = (int)$id_forma_pago;


if ($id_forma_pago === 1) {
   $recargo = 0;
}



/* "SELECT a.tiposervicio tipo,a.servicio descripcion,a.id_atencion,a.id_servicio,a.id_cliente,a.fecha fecha_atencion, 
 b.id_tipo_precio,b.precio+(b.precio*$recargo) precio,1 cantidad, b.precio+(b.precio*$recargo) subtotal
FROM vw_servicios_paciente a
JOIN tbl_precio_servicio b ON b.id_servicio=a.id_servicio
WHERE a.id_cliente = ?
    AND DATE(a.fecha) = ?
    AND b.id_tipo_precio = ?
" */
$query = 
"SELECT 
    a.tiposervicio tipo,
    a.servicio descripcion,
    a.id_atencion,
    a.id_servicio,
    a.id_cliente,
    a.fecha fecha_atencion,
    a.precio precioconsulta,
    b.id_tipo_precio,
    CASE 
        WHEN a.precio > 0 THEN a.precio+(a.precio*$recargo)
        ELSE b.precio+(b.precio*$recargo)
    END AS precio,
    1 cantidad,
    CASE 
        WHEN a.precio > 0 THEN a.precio+(a.precio*$recargo)
        ELSE b.precio+(b.precio*$recargo)
    END AS subtotal
FROM vw_servicios_paciente a
JOIN tbl_precio_servicio b ON b.id_servicio = a.id_servicio
WHERE a.id_cliente = ?
    AND DATE(a.fecha) = ?
    AND b.id_tipo_precio = ?"
;


$stmt = $conn->prepare($query);
$stmt->bind_param('isi', $id_cliente, $fecha, $id_tipo_precio);
$stmt->execute();
$result = $stmt->get_result();
$total_general = 0;

while ($row = $result->fetch_assoc()) {
    $tipo = $row['tipo'];
    // Inicializar grupo si no existe
    if (!isset($data[$tipo])) {
        $data[$tipo] = [];
        $totales[$tipo] = 0;
    }

    $subtotal = (float) $row['subtotal'];
    $row['precio'] = number_format($row['precio'], 2);
    $row['subtotal'] = number_format($subtotal, 2);

    $data[$tipo][] = $row;
    $totales[$tipo] += $subtotal;
    $total_general += $subtotal;
}

$response = [
    'grupos' => [],
    'total_general' => number_format($total_general, 2)
];

// Formatear resultado
foreach ($data as $tipo => $items) {
    $response['grupos'][] = [
        'tipo' => $tipo,
        'items' => $items,
        'subtotal' => number_format($totales[$tipo], 2)
    ];
}

echo json_encode($response);
?>
