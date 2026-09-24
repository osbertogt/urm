<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

// Validar fechas
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';
if (!$desde || !$hasta) {
  echo json_encode(['data'=>[], 'totales'=>['global'=>0]]);
  exit;
}

// Escapar
$desde = mysqli_real_escape_string($conn, $desde);
$hasta = mysqli_real_escape_string($conn, $hasta);

// Obtener tipos de pago dinámicamente
$formas_pago = [];
$res_fp = $conn->query("SELECT id, nombre FROM cat_formapago ORDER BY id ASC");
while ($r = $res_fp->fetch_assoc()) {
  $formas_pago[$r['id']] = $r['nombre'];
}

// Consulta combinada
$sql = "
SELECT c.fecha, c.codigo, 
       CONCAT(p.nombre_1, ' ', p.nombre_2, ' ', p.apellido_1, ' ', p.apellido_2) AS cliente,
       c.id_tipo_pago1 id_tipo_pago_1, c.total monto_1, c.id_tipo_pago2 id_tipo_pago_2, c.monto_2, c.id_tipo_pago3 id_tipo_pago_3, c.monto_3
FROM tbl_cuenta_cliente c
JOIN tbl_persona p ON p.id = c.id_cliente
WHERE fecha BETWEEN ? AND ?
UNION ALL
SELECT cp.fecha, cc.codigo,
       CONCAT(p.nombre_1, ' ', p.nombre_2, ' ', p.apellido_1, ' ', p.apellido_2) AS cliente,
       cp.id_forma_pago1 id_tipo_pago_1, cp.monto_1, cp.id_forma_pago2 id_tipo_pago_2, cp.monto_2, cp.id_forma_pago3 id_tipo_pago_3, cp.monto_3
FROM tbl_cuenta_cliente_pago cp
JOIN tbl_cuenta_cliente cc ON cc.id = cp.id_cuenta
JOIN tbl_persona p ON p.id = cc.id_cliente
WHERE cp.fecha BETWEEN ? AND ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $desde, $hasta, $desde, $hasta);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
$totales = array_fill_keys(array_map(fn($id) => "fp_$id", array_keys($formas_pago)), 0);
$totales['global'] = 0;

while ($r = $res->fetch_assoc()) {
  $fila = [
    'fecha' => $r['fecha'],
    'codigo' => htmlspecialchars($r['codigo']),
    'cliente' => htmlspecialchars(trim(preg_replace('/\s+/', ' ', $r['cliente'])))
  ];

  // Inicializar montos por forma de pago
  foreach ($formas_pago as $id => $nombre) {
    $fila[$nombre] = "0.00";
  }

  // Registrar montos según tipo de pago
  $map = [
    $r['id_tipo_pago_1'] => $r['monto_1'],
    $r['id_tipo_pago_2'] => $r['monto_2'],
    $r['id_tipo_pago_3'] => $r['monto_3']
  ];

  $total = 0;
  foreach ($map as $id => $monto) {
    if ($id && isset($formas_pago[$id]) && $monto !== null) {
      $fila[$formas_pago[$id]] = number_format((float)$monto, 2);
      $totales["fp_$id"] += (float)$monto;
      $total += (float)$monto;
    }
  }

  $fila['total'] = number_format($total, 2);
  $totales['global'] += $total;
  $data[] = $fila;
}

echo json_encode([
  'data' => $data,
  'totales' => $totales
]);
exit;
?>
