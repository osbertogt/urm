<?php
require_once '../../assets/dbc.php';

$id = $_POST['id_atencion'] ?? null;
$id_medico = $_POST['id_medico'] ?? null;
$id_cliente = $_POST['id_cliente'] ?? null;
$fecha = $_POST['inputFecha'] ?? null;
$fecha_entrega = $_POST['fecha_entrega_resultado'] ?? null;
$servicios = $_POST['servicios'] ?? [];
$id_tipoatencion=1;
$stat = isset($_POST['inputStat']) ? 1 : 0; 
$id_estado = 1;


if ($id) {
    // Actualizar
    $stmt = $conn->prepare("UPDATE tbl_atencion SET id_medico=?, fecha=?, fecha_entrega_resultado=?, stat=? WHERE id=?");
    $stmt->bind_param("isssi", $id_medico, $fecha, $fecha_entrega, $stat, $id);
    $stmt->execute();

    $conn->query("DELETE FROM tbl_atencion_servicio WHERE id_atencion = $id");
} else {
    // Insertar
    $ticket = strtoupper(uniqid("TK"));
    $stmt = $conn->prepare("INSERT INTO tbl_atencion (id_tipo_atencion, numero_ticket, fecha, fecha_entrega_resultado, id_medico, stat, id_cliente, id_estado)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssiiii", $id_tipoatencion, $ticket, $fecha, $fecha_entrega, $id_medico, $stat, $id_cliente, $id_estado);
    $stmt->execute();
    $id = $stmt->insert_id;
}

// Insertar servicios
$stmt2 = $conn->prepare("INSERT INTO tbl_atencion_servicio (id_atencion, id_servicio) VALUES (?, ?)");
foreach ($servicios as $id_servicio) {
    $stmt2->bind_param("ii", $id, $id_servicio);
    $stmt2->execute();
}

echo "ok";
