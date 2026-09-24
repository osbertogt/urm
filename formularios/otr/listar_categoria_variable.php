<?php
require_once '../../assets/dbc.php';

$sql = "SELECT cv.id, CONCAT(ta.nombre, ' - ', cv.nombre) AS nombre
        FROM cat_categoria_variable cv
        INNER JOIN cat_tipo_analisis ta ON cv.id_tipo_analisis = ta.id
        ORDER BY ta.nombre, cv.nombre";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
?>
