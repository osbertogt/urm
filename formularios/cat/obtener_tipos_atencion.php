<?php
require '../../assets/dbc.php';
header('Content-Type: application/json');

$res = $conn->query("SELECT id, nombre FROM cat_tipo_atencion ORDER BY nombre");
$data = $res->fetch_all(MYSQLI_ASSOC);
echo json_encode($data);
