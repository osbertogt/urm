<?php
require_once '../../assets/dbc.php';
$sql = "SELECT id, nombre FROM cat_servicio ORDER BY nombre";
$result = $conn->query($sql);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>