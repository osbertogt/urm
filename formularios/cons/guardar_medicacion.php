<?php
session_start();
include_once '../../assets/dbc.php';
header('Content-Type: application/json');
$id_cliente = $_SESSION['cliente_activo'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    //exit;
}

// Validar datos
$id = filter_input(INPUT_POST, 'id_medicacion', FILTER_VALIDATE_INT);
$id_atencion = filter_input(INPUT_POST, 'id_atencion', FILTER_VALIDATE_INT);
$fecha = filter_input(INPUT_POST, 'fecha_medicacion', FILTER_SANITIZE_SPECIAL_CHARS);
$medicamentos = filter_input(INPUT_POST, 'medicamentos', FILTER_SANITIZE_SPECIAL_CHARS);
$observaciones = filter_input(INPUT_POST, 'observaciones_med', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$fecha || !$medicamentos) {
    //echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    //exit;
}

try {
    if ($id) {
        // Actualizar registro existente
        $query = "UPDATE tbl_atencion_consulta_med 
                 SET fecha = ?, medicamentos = ?, observaciones = ? 
                 WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $fecha, $medicamentos, $observaciones, $id);
    } else {
        // Insertar nuevo registro
        $query = "INSERT INTO tbl_atencion_consulta_med (id_cliente, fecha, medicamentos, observaciones) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $id_cliente, $fecha, $medicamentos, $observaciones);
    }
    
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'Medicación guardada correctamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>