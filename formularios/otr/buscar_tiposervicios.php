<?php
require '../../assets/dbc.php';

$term = $_GET['term'] ?? '';

$sql = "SELECT a.id,a.nombre,0 precio 
		FROM cat_tipo_atencion a
		WHERE  a.nombre LIKE ?";

$stmt = $conn->prepare($sql);
$like = "%$term%";
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
