<?php
require_once '../../assets/dbc.php';

$id = $_POST['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM tbl_medico_horario_atencion WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error: " . $stmt->error;
    }
}
