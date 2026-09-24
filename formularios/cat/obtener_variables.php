<?php
require_once '../../assets/dbc.php';

$id_servicio = isset($_GET['id_servicio']) ? intval($_GET['id_servicio']) : 0;

$sql = "
SELECT 
    cv.id,
    cv.nombre,
    cv.referencia,
    cv.valor_normal_maximo,
    cv.valor_normal_minimo,
    um.nombre AS unidad,
    ccv.id AS id_cat_categoria_variable,
	ccv.nombre AS categoria,
    um.id AS id_unidad_medida_variable
FROM cat_variable cv
JOIN cat_categoria_variable ccv ON ccv.id = cv.id_cat_categoria_variable
JOIN cat_unidad_medida_variable um ON um.id = cv.id_unidad_medida_variable
WHERE ccv.id_tipo_analisis = (
    SELECT id_tipo_analisis FROM cat_servicio WHERE id = ?
)
ORDER BY cv.id ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_servicio);
$stmt->execute();
$result = $stmt->get_result();

$variables = [];
while ($row = $result->fetch_assoc()) {
    $variables[] = $row;
}

header('Content-Type: application/json');
echo json_encode($variables);

?>