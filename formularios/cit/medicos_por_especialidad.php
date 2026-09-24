<?php
require_once '../../assets/dbc.php';
$id_especialidad = $_GET['id_especialidad'] ?? null;

$sql = "SELECT id, CONCAT_WS(' ', nombre_1, nombre_2, apellido_1, apellido_2) AS nombre FROM tbl_persona WHERE id_especialidad = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_especialidad);
$stmt->execute();
$result = $stmt->get_result();
$options = [];
while ($row = $result->fetch_assoc()) {
    $options[] = $row;
}
echo json_encode($options);
?>