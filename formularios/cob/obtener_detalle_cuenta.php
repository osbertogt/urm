<?php
require_once '../../assets/dbc.php';

header('Content-Type: application/json');
$id_cliente = $_GET['id_cliente'];
$fecha = $_GET['fecha'];
$id_tipo_precio = $_GET['id_tipo_precio'];

$query = "SELECT tipo, descripcion, cantidad, precio, (cantidad * precio) AS subtotal FROM vw_cuenta_cliente WHERE id_cliente=? AND fecha_atencion=? AND id_tipo_precio=? ORDER BY tipo";
$stmt = $conn->prepare($query);
$stmt->bind_param('isi', $id_cliente, $fecha, $id_tipo_precio);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>