<?php
require_once '../../assets/dbc.php';

$id = $_POST['id'] ?? null;
$id_medico = $_POST['id_medico'] ?? null;
$dia = $_POST['dia_semana'] ?? null;
$hora = $_POST['hora_inicio'] ?? null;
$hora2 = $_POST['hora_final'] ?? null;
if ($id) {
    // Actualizar
    $stmt = $conn->prepare("UPDATE tbl_medico_horario_atencion SET id_medico=?, dia_semana=?, hora=?, hora2=? WHERE id=?");
    $stmt->bind_param("isssi", $id_medico, $dia, $hora, $hora2, $id);
} else {
    // Insertar
    $stmt = $conn->prepare("INSERT INTO tbl_medico_horario_atencion (id_medico, dia_semana, hora, hora2) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $id_medico, $dia, $hora, $hora2);
}

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "error: " . $stmt->error;
}
