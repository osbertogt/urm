<?php
require_once '../../assets/dbc.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("SELECT id, id_medico, dia_semana, hora FROM tbl_medico_horario_atencion WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
}
