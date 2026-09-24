<?php
session_start();
require_once '../../assets/dbc.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Validar sesión
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action !== 'generar_orden_interna') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

// Sanitizar y validar datos
$id_cita = intval($_POST['id_cita'] ?? 0);
$id_cliente = intval($_POST['id_cliente'] ?? 0);
$id_medico = intval($_POST['id_medico'] ?? 0);
$nombre_medico = trim($_POST['nombre_medico'] ?? '');
$paciente = trim($_POST['paciente'] ?? '');
$servicios_json = $_POST['servicios'] ?? '[]';
$id_tipo_atencion=3;


// Validaciones básicas
if ($id_cliente <= 0 || $id_medico <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'Datos del paciente o médico inválidos'
    ]);
    exit;
}

// Decodificar servicios
$servicios = json_decode($servicios_json, true);
if (!is_array($servicios) || empty($servicios)) {
    echo json_encode([
        'success' => false, 
        'message' => 'No hay servicios válidos para generar la orden'
    ]);
    exit;
}

try {
    // Iniciar transacción
    $conn->begin_transaction();
    
    // 1. Insertar en tbl_atencion
    $fecha_actual = date('Y-m-d H:i:s');
    $notas = "Orden de laboratorio generada por " . $nombre_medico;
	$query_max = "SELECT COALESCE(MAX(CAST(numero_ticket AS UNSIGNED)), 0) + 1 as siguiente_ticket 
              FROM tbl_atencion";
	$result = $conn->query($query_max);
	$row = $result->fetch_assoc();
	$nuevoNumeroTicket = $row['siguiente_ticket'];
    
    $sql_atencion = "INSERT INTO tbl_atencion 
                     (id_tipo_atencion, numero_ticket, id_cliente, id_medico, fecha, notas) 
                     VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql_atencion);
    if (!$stmt) {
        throw new Exception("Error preparando consulta de atención: " . $conn->error);
    }
    
    $stmt->bind_param("isiiss", $id_tipo_atencion, $nuevoNumeroTicket, $id_cliente, $id_medico, $fecha_actual, $notas);
    
    if (!$stmt->execute()) {
        throw new Exception("Error insertando atención: " . $stmt->error);
    }
    
    $id_atencion = $conn->insert_id;
    $stmt->close();
    
    // 2. Insertar servicios en tbl_atencion_servicio
    $sql_servicio = "INSERT INTO tbl_atencion_servicio 
                     (id_atencion, id_servicio) 
                     VALUES (?, ?)";
    
    $stmt_servicio = $conn->prepare($sql_servicio);
    if (!$stmt_servicio) {
        throw new Exception("Error preparando consulta de servicios: " . $conn->error);
    }
    
    $servicios_insertados = 0;
    foreach ($servicios as $servicio) {
        $id_servicio = intval($servicio['id'] ?? 0);
        
        if ($id_servicio > 0) {
            $stmt_servicio->bind_param("ii", $id_atencion, $id_servicio);
            
            if (!$stmt_servicio->execute()) {
                throw new Exception("Error insertando servicio ID {$nuevoNumeroTicket}: " . $stmt_servicio->error);
            }
            
            $servicios_insertados++;
        }
    }
    
    $stmt_servicio->close();
    
    // 3. Confirmar transacción
    $conn->commit();
    
    // 4. Registrar en log (opcional)
    error_log("Orden interna generada - ID: $nuevoNumeroTicket, Cliente: $id_cliente, Servicios: $servicios_insertados");
    
    // 5. Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'orden_id' => $id_atencion,
        'message' => "Orden interna No. $nuevoNumeroTicket generada exitosamente. " .
                    "Se registraron $servicios_insertados servicio(s) para el paciente $paciente.",
        'servicios_insertados' => $servicios_insertados,
        'fecha' => $fecha_actual
    ]);
    
} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    
    error_log("Error generando orden interna: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar orden interna: ' . $e->getMessage()
    ]);
}

// Cerrar conexión
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>