<?php
require_once '../../assets/dbc.php';

$tabla = $_POST['tabla'] ?? '';
$id = $_POST['id'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');

$permitidas = ['cat_categoria_servicio', 'cat_categoria_variable', 'cat_unidad_medida_variable', 
'cat_especialidad', 'cat_unidad_medida', 'cat_presentacion','cat_bodega','cat_inv_proveedor',
'cat_inv_motivo','cat_fabricante','cat_tipo_atencion','cat_tipo_precio','cat_vacuna','cat_dosis'];

if (!in_array($tabla, $permitidas) || $nombre === '') {
    exit('Error');
}

if ($id === '') {
    $stmt = $conn->prepare("INSERT INTO $tabla (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre);
} else {
    $stmt = $conn->prepare("UPDATE $tabla SET nombre=? WHERE id=?");
    $stmt->bind_param("si", $nombre, $id);
}
$stmt->execute();
echo 'OK';
?>