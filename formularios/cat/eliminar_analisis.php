<?php
require '../../assets/dbc.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? 0;
$stmt = $conn->prepare("DELETE FROM cat_servicio WHERE id=?");
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
echo json_encode(['status'=>$ok ? 'success':'error', 'message'=>$stmt->error]);
