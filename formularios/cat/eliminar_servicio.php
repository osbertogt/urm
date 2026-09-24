<?php
require_once '../../assets/dbc.php';

$id = intval($_POST['id']);

if ($id <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM cat_servicio WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["ok" => true]);
} else {
    echo json_encode(["ok" => false, "msg" => "No se pudo eliminar"]);
}
$stmt->close();
?>