<?php
require_once '../../assets/dbc.php';

$id = $_POST['id_variable'] ?? null;
$id_tipo_analisis = $_POST['id_tipo_analisis'] ?? null;
$id_categoria = $_POST['id_categoria_variable'] ?? null;
$id_unidad = $_POST['id_unidad_medida'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$minimo = $_POST['valor_minimo'] ?? null;
$maximo = $_POST['valor_maximo'] ?? null;
$referencia = $_POST['referencia'] ?? '';

if ($id) {
    $stmt = $conn->prepare("UPDATE cat_variable SET id_cat_categoria_variable=?, id_unidad_medida_variable=?, nombre=?, valor_normal_minimo=?, valor_normal_maximo=?, referencia=? WHERE id=?");
    $stmt->bind_param("iissssi", $id_categoria, $id_unidad, $nombre, $minimo, $maximo, $referencia, $id);
} else {
    $stmt = $conn->prepare("INSERT INTO cat_variable (id_cat_categoria_variable, id_unidad_medida_variable, nombre, valor_normal_minimo, valor_normal_maximo, referencia) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $id_categoria, $id_unidad, $nombre, $minimo, $maximo, $referencia);
}

$stmt->execute();
$stmt->close();
echo "ok";
?>
