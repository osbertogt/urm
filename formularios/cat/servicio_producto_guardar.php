<?php
require_once '../../assets/dbc.php';
$id_servicio = $_GET['id_servicio'] ?? 0;
$datos = [];

$sql = "SELECT p.id, p.nombre, sps.cantidad
        FROM tbl_productos_servicios sps
        JOIN cat_producto p ON p.id = sps.id_producto
        WHERE sps.id_servicio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_servicio);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $datos[] = [
    'id' => $row['id'],
    'nombre' => $row['nombre'],
    'cantidad' => $row['cantidad']
  ];
}

header('Content-Type: application/json');
echo json_encode($datos);
?>
