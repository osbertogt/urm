<?php
require_once '../../assets/dbc.php';

$id = $_GET['id'] ?? null;
if (!$id) {
  http_response_code(400);
  exit('ID inválido');
}

$sql = "SELECT * FROM cat_variable WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$variable = $result->fetch_assoc();

echo json_encode($variable);
?>
