<?php
// obtener_notificaciones_lab.php
session_start();
require_once '../assets/dbc.php';

header('Content-Type: application/json');

// Solo usuarios del área de laboratorio
if ($_SESSION['id_area'] != 2) {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        no.id,
        no.id_orden,
        no.titulo,
        no.mensaje,
        no.fecha_creacion,
        u.nombre as usuario_creador
    FROM tbl_notificaciones_orden no
    JOIN tbl_usuario u ON no.id_usuario_creador = u.id
    WHERE no.id_area_destino = 2 
    AND no.leida = FALSE
    AND no.fecha_creacion >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ORDER BY no.fecha_creacion DESC
    LIMIT 5
");

$stmt->execute();
$result = $stmt->get_result();
$notificaciones = [];

while ($row = $result->fetch_assoc()) {
    $notificaciones[] = $row;
    
    // Marcar como leída después de enviar
    $update = $conn->prepare("UPDATE tbl_notificaciones_orden SET leida = TRUE WHERE id = ?");
    $update->bind_param("i", $row['id']);
    $update->execute();
}

echo json_encode([
    'success' => true,
    'notificaciones' => $notificaciones
]);
?>
