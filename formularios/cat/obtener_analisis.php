<?php
require '../../assets/dbc.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT id, nombre, id_tipo_atencion, costo FROM cat_servicio WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
echo json_encode($res->fetch_assoc());
