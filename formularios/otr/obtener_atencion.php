<?php
require_once '../../assets/dbc.php';

$id = intval($_GET['id']);
$data = [];

// Atención
$stmt = $conn->prepare("SELECT * FROM tbl_atencion WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Servicios
$sql = "SELECT s.id, s.nombre, s.precio 
        FROM tbl_atencion_servicio tas
        JOIN cat_servicio s ON tas.id_servicio = s.id
        WHERE tas.id_atencion = ?";
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$res = $stmt2->get_result();

$data['servicios'] = [];
while ($row = $res->fetch_assoc()) {
    $data['servicios'][] = $row;
}

echo json_encode($data);