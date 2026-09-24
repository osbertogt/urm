<?php
require_once '../../assets/dbc.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nombre = trim($_POST['nombre']);
$id_tipo = intval($_POST['id_tipo_analisis']);

if (!$nombre || !$id_tipo) {
    echo json_encode(["ok" => false, "msg" => "Datos incompletos"]);
    exit;
}

if ($id > 0) {
    // Editar
    $stmt = $conn->prepare("UPDATE cat_servicio SET nombre = ?, id_tipo_analisis = ? WHERE id = ?");
    $stmt->bind_param("sii", $nombre, $id_tipo, $id);
} else {
    // Insertar
    $stmt2 = $conn->prepare("INSERT INTO cat_categoria_variable (nombre, id_tipo_analisis) VALUES (?, ?)");
    $stmt2->bind_param("si", $nombre, $id_tipo);
    $stmt = $conn->prepare("INSERT INTO cat_servicio (nombre, id_tipo_analisis) VALUES (?, ?)");
    $stmt->bind_param("si", $nombre, $id_tipo);
}

if ($stmt->execute()) {
	$stmt2->execute();
    echo json_encode(["ok" => true]);
} else {
    echo json_encode(["ok" => false, "msg" => "Error al guardar"]);
}
$stmt->close();
?>