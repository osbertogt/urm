<?php
require_once '../../assets/dbc.php';

$id_tipo = intval($_GET['id_tipo_analisis']);
$sql = "SELECT id, nombre FROM cat_categoria_variable WHERE id_tipo_analisis = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_tipo);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>