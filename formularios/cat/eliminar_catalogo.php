<?php
require_once '../../assets/dbc.php';

$tabla = $_POST['tabla'] ?? '';
$id = $_POST['id'] ?? '';

$permitidas = ['cat_categoria_servicio', 'cat_categoria_variable', 'cat_unidad_medida_variable', 
'cat_especialidad', 'cat_unidad_medida', 'cat_presentacion','cat_bodega','cat_inv_proveedor',
'cat_inv_motivo','cat_fabricante','cat_tipo_atencion','cat_tipo_precio','cat_vacuna','cat_dosis'];

if (!in_array($tabla, $permitidas) || !is_numeric($id)) {
    exit('Error');
}

$stmt = $conn->prepare("DELETE FROM $tabla WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
echo 'OK';
?>