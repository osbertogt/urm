<?php
header('Content-Type: application/json');
require_once '../../assets/dbc.php';

// Obtener y limpiar datos SIN htmlspecialchars
$id_cliente = intval($_POST['id_cliente']);
$id_cita = intval($_POST['id_cita']);
$fecha_hora = trim($_POST['fecha_hora'] ?? '');

$presion = trim($_POST['presion'] ?? '');
$pulso = trim($_POST['pulso'] ?? '');
$respiracion = trim($_POST['respiracion'] ?? '');
$temperatura = $_POST['temperatura'];

$id_tipo_atencion = 9;

// Debug: ver qué llega
error_log("temperatura recibida: " . $temperatura);

$sql_check = "SELECT id FROM tbl_atencion WHERE id_cita = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $id_cita);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // Ya existe: obtener id_atencion
    $stmt_check->bind_result($id_atencion);
    $stmt_check->fetch();

    // Actualizar tbl_atencion_consulta
    $sql_update2 = "UPDATE tbl_atencion_consulta
                    SET fecha_hora = ?, 
                        sv_presion_arterial = ?, 
                        sv_frecuencia_cardiaca = ?, 
                        sv_frecuencia_respiratoria = ?, 
                        sv_temperatura = ?
                    WHERE id_atencion = ?";
    $stmt2 = $conn->prepare($sql_update2);
    
    $stmt2->bind_param("sssssi", 
        $fecha_hora, 
        $presion,       
        $pulso,         
        $respiracion,   
        $temperatura,   
        $id_atencion
    );
    
    if ($stmt2->execute()) {
        error_log("temperatura guardada en UPDATE: " . $temperatura);
    } else {
        error_log("Error UPDATE: " . $stmt2->error);
    }
    $stmt2->close();
} else {
    // No existe: crear nueva atención
    
    // Obtener número de ticket siguiente
    $query_max = "SELECT COALESCE(MAX(CAST(numero_ticket AS UNSIGNED)), 0) + 1 AS siguiente_ticket FROM tbl_atencion";
    $result = $conn->query($query_max);
    $row = $result->fetch_assoc();
    $nuevoNumeroTicket = $row['siguiente_ticket'];
    
    // 1. Insertar en tbl_atencion
    $query1 = "INSERT INTO tbl_atencion (id_cita, numero_ticket, id_cliente, id_tipo_atencion, fecha)
               VALUES (?, ?, ?, ?, ?)";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("isiis", $id_cita, $nuevoNumeroTicket, $id_cliente, $id_tipo_atencion, $fecha_hora);
    $stmt1->execute();
    $id_atencion = $conn->insert_id;
    $stmt1->close();
    
    // 2. Insertar en tbl_atencion_consulta
    $query2 = "INSERT INTO tbl_atencion_consulta (fecha_hora, id_atencion, id_tipo_atencion, 
                sv_presion_arterial, sv_frecuencia_cardiaca, sv_frecuencia_respiratoria, sv_temperatura)
               VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt2 = $conn->prepare($query2);
    
    // IMPORTANTE: Todos como strings "s" para mantener formato
    $stmt2->bind_param("siissss", 
        $fecha_hora, 
        $id_atencion, 
        $id_tipo_atencion,
        $presion,       
        $pulso,         
        $respiracion,   
        $temperatura    
    );
    
    if ($stmt2->execute()) {
        error_log("Presión guardada en INSERT: " . $presion);
    } else {
        error_log("Error INSERT: " . $stmt2->error);
    }
    $stmt2->close();
    $accion = 'registrada';
}

$stmt_check->close();
$conn->close();

echo json_encode(['status' => 'ok', 'presion_guardada' => $presion]);
?>