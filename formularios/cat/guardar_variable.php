<?php
require_once '../../assets/dbc.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$id_servicio = isset($_POST['id_servicio']) ? intval($_POST['id_servicio']) : 0;
$id_cat_categoria_variable = isset($_POST['id_cat_categoria_variable']) ? intval($_POST['id_cat_categoria_variable']) : 0;
$id_unidad_medida_variable = isset($_POST['id_unidad_medida_variable']) ? intval($_POST['id_unidad_medida_variable']) : 0;
$nombre = trim($_POST['nombre'] ?? '');
$referencia = trim($_POST['referencia'] ?? '');
$valor_max = $_POST['valor_normal_maximo'] !== '' ? floatval($_POST['valor_normal_maximo']) : null;
$valor_min = $_POST['valor_normal_minimo'] !== '' ? floatval($_POST['valor_normal_minimo']) : null;

if (!$id_servicio || !$id_cat_categoria_variable || !$id_unidad_medida_variable || $nombre === '') {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
    exit;
}

if ($id > 0) {
    // UPDATE
    $sql = "UPDATE cat_variable 
            SET id_cat_categoria_variable=?, id_unidad_medida_variable=?, nombre=?, referencia=?, valor_normal_maximo=?, valor_normal_minimo=?
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissddi", $id_cat_categoria_variable, $id_unidad_medida_variable, $nombre, $referencia, $valor_max, $valor_min, $id);
} else {
    // INSERT
    $sql = "INSERT INTO cat_variable (id_cat_categoria_variable, id_unidad_medida_variable, nombre, referencia, valor_normal_maximo, valor_normal_minimo)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdd", $id_cat_categoria_variable, $id_unidad_medida_variable, $nombre, $referencia, $valor_max, $valor_min);
}

if ($stmt->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Error en base de datos: ' . $conn->error]);
}
?>