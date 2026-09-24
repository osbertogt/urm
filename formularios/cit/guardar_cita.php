<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../assets/dbc.php';
    
    $id = $_POST['id'] ?? null;
    $id_medico = $_POST['id_medico'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $id_cliente = $_POST['id_cliente'] ?? 0;
    $nombre = $_POST['paciente_nombre'];
    $telefono = $_POST['paciente_telefono'];
    $email = $_POST['paciente_email'];
    $estado = 1;

    if ($id) {
        $stmt = $conn->prepare("UPDATE tbl_cita SET id_medico=?, fecha=?, hora=?, paciente_nombre=?, paciente_telefono=?, paciente_email=?, id_cliente=? WHERE id=?");
        $stmt->bind_param("isssssii", $id_medico, $fecha, $hora, $nombre, $telefono, $email, $id_cliente, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO tbl_cita (id_medico, fecha, hora, paciente_nombre, paciente_telefono, paciente_email, id_estado, id_cliente) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssii", $id_medico, $fecha, $hora, $nombre, $telefono, $email, $estado, $id_cliente);
        $stmt->execute();
    }
    echo "ok";
}
?>