<?php
include_once '../../assets/dbc.php';
$id = $_GET['id'];

$sql = "SELECT * FROM tbl_persona WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$cliente = $res->fetch_assoc();

echo json_encode($cliente);
?>
