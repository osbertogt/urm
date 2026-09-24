<?php
require_once '../../assets/dbc.php';
$id = $_POST['id'] ?? null;
$estado = $_POST['estado'] ?? null;
if ($id && $estado) {
    $stmt = $conn->prepare("UPDATE tbl_cita SET id_estado = ? WHERE id = ?");
    $stmt->bind_param("ii", $estado, $id);
    $stmt->execute();
    echo "ok";
}
?>