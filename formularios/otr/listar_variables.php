<?php
require_once '../../assets/dbc.php';

$sql = "SELECT 
  v.id,
  v.nombre,
  v.valor_normal_minimo AS valor_minimo,
  v.valor_normal_maximo AS valor_maximo,
  v.referencia,
  ta.nombre AS tipo_analisis,
  cv.nombre AS categoria,
  um.nombre AS unidad
FROM cat_variable v
INNER JOIN cat_categoria_variable cv ON v.id_cat_categoria_variable = cv.id
INNER JOIN cat_tipo_analisis ta ON cv.id_tipo_analisis = ta.id
INNER JOIN cat_unidad_medida_variable um ON v.id_unidad_medida_variable = um.id";

$result = $conn->query($sql);
$variables = [];

while ($row = $result->fetch_assoc()) {
    $variables[] = $row;
}

echo json_encode(['data' => $variables]);
?>
