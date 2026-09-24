<?php
// listar_vw_citas.php
// Devuelve registros de la vista vw_citas (solo citas pendientes o futuras)

require '../../assets/dbc.php'; // ajusta la ruta a tu conexión mysqli
header('Content-Type: application/json; charset=utf-8');

// Evitar caché
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Consulta: solo citas con fecha igual o posterior a hoy
$sql = "SELECT id, id_medico, id_cliente, fecha, hora, medico, 
               paciente_nombre, paciente_telefono, paciente_email, estado
        FROM vw_citas
        WHERE fecha >= CURDATE()
        ORDER BY fecha ASC, hora ASC";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar citas: ' . $conn->error]);
    exit;
}

$citas = [];

while ($row = $result->fetch_assoc()) {
    $citas[] = [
        'id' => (int)$row['id'],
        'id_medico' => (int)$row['id_medico'],
        'id_cliente' => (int)$row['id_cliente'],
        'fecha' => htmlspecialchars($row['fecha']),
        'hora' => htmlspecialchars($row['hora']),
        'medico' => htmlspecialchars($row['medico']),
        'paciente_nombre' => htmlspecialchars($row['paciente_nombre']),
        'paciente_telefono' => htmlspecialchars($row['paciente_telefono']),
        'paciente_email' => htmlspecialchars($row['paciente_email']),
        'estado' => htmlspecialchars($row['estado'])
    ];
}

echo json_encode($citas, JSON_UNESCAPED_UNICODE);
exit;
