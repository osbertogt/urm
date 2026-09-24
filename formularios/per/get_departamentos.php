<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
$like = "%{$term}%";

$sql = "SELECT id, nombre FROM cat_departamento WHERE nombre LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "id" => $row["id"],
        "text" => $row["nombre"]
    ];
}

echo json_encode(["results" => $data]);

