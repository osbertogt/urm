<?php
require_once '../../assets/dbc.php';

header('Content-Type: application/json');

$tabla = $_POST['tabla'] ?? '';
$permitidas = ['cat_categoria_servicio', 'cat_categoria_variable', 'cat_unidad_medida_variable', 
'cat_especialidad', 'cat_unidad_medida', 'cat_presentacion','cat_bodega','cat_inv_proveedor',
'cat_inv_motivo','cat_fabricante','cat_tipo_atencion','cat_tipo_precio','cat_vacuna','cat_dosis'];

if (!in_array($tabla, $permitidas)) {
    echo json_encode(['data' => []]);
    exit;
}

$stmt = $conn->prepare("SELECT id, nombre FROM $tabla");
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data]);
?>