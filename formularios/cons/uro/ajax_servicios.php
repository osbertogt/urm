<?php
session_start();
require_once '../../../assets/dbc.php'; 
header('Content-Type: application/json');


$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'buscar_servicios':
            buscarServicios($conn);
            break;
            
        case 'listar_servicios':
            listarServicios($conn);
            break;
            
        case 'get_servicio':
            getServicio($conn);
            break;
            
        case 'guardar_servicio':
            guardarServicio($conn);
            break;
            
        case 'eliminar_servicio':
            eliminarServicio($conn);
            break;
            
        case 'get_servicios_pdf':
            getServiciosPDF($conn);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Funciones

function buscarServicios($conn) {
    
    $term = $_POST['term'] ?? '';
    
    $sql = "SELECT id, nombre FROM cat_servicio 
            WHERE id_tipo_atencion = 1 
            AND (nombre LIKE ? OR id LIKE ?)
            ORDER BY nombre
            LIMIT 50";
    
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$term%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $servicios = [];
        
        while ($row = $result->fetch_assoc()) {
            $servicios[] = [
                'id' => $row['id'],
                'text' => $row['nombre']
            ];
        }
        
        $response = [
            'results' => $servicios,
            'pagination' => ['more' => false]
        ];
    } else {
        // Error en consulta
        $response = [
            'results' => [],
            'pagination' => ['more' => false],
            'error' => 'Error en la base de datos'
        ];
    }
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

function listarServicios($conn) {
    $id_cita = intval($_POST['id_cita'] ?? 0);
    
    if ($id_cita <= 0) {
        echo json_encode([]);
        return;
    }
    
    // Obtener ID de atención
    $sql_atencion = "SELECT id FROM tbl_atencion WHERE id_cita = ? LIMIT 1";
    $stmt = $conn->prepare($sql_atencion);
    $stmt->bind_param("i", $id_cita);
    $stmt->execute();
    $result = $stmt->get_result();
    $atencion = $result->fetch_assoc();
    
    if (!$atencion) {
        echo json_encode([]);
        return;
    }
    
    $id_atencion = $atencion['id'];
    
    // Obtener servicios
    $sql = "SELECT 
                al.id,
				al.id_atencion,
                al.fecha as fecha,
                al.id_servicio,
                al.notas,
                cs.nombre as nombre_servicio
            FROM tbl_atencion_laboratorios al
			JOIN tbl_atencion ta ON ta.id=al.id_atencion
            JOIN cat_servicio cs ON al.id_servicio = cs.id
            WHERE ta.id_cita = ?
            ORDER BY al.fecha DESC, al.id DESC;";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cita);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $servicios = [];
    while ($row = $result->fetch_assoc()) {
        $servicios[] = [
            'id' => $row['id'],
            'fecha' => $row['fecha'],
            'id_atencion' => $row['id_atencion'],
            'id_servicio' => $row['id_servicio'],
            'nombre_servicio' => $row['nombre_servicio'],
            'notas' => $row['notas']
        ];
    }
    
    echo json_encode($servicios);
}

function getServicio($conn) {
    $id = intval($_POST['id'] ?? 0);
    
    $sql = "SELECT nombre FROM cat_servicio WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $servicio = $result->fetch_assoc();
    
    if ($servicio) {
        echo json_encode(['success' => true, 'nombre' => $servicio['nombre']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
    }
}

function guardarServicio($conn) {
    // Habilitar reporte de errores
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $id = intval($_POST['id'] ?? 0);
    $id_cita = intval($_POST['id_cita'] ?? 0);
    $id_servicio = intval($_POST['id_servicio'] ?? 0);
    $notas = trim($_POST['notas'] ?? '');
    
    // Validaciones
    if ($id_cita <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'ID de cita inválido: ' . $id_cita,
            'debug' => ['id_cita_received' => $_POST['id_cita'] ?? 'null']
        ]);
        return;
    }
    
    if ($id_servicio <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'ID de servicio inválido: ' . $id_servicio,
            'debug' => ['id_servicio_received' => $_POST['id_servicio'] ?? 'null']
        ]);
        return;
    }
    
    try {
        // DEBUG: Log inicial
        error_log("=== guardarServicio ===");
        error_log("id_cita: $id_cita, id_servicio: $id_servicio, id: $id");
        
        // 1. Obtener o crear atención
        $sql_atencion = "SELECT id FROM tbl_atencion WHERE id_cita = ? LIMIT 1";
        error_log("Consulta atención: $sql_atencion con id_cita: $id_cita");
        
        $stmt = $conn->prepare($sql_atencion);
        $stmt->bind_param("i", $id_cita);
        $stmt->execute();
        $result = $stmt->get_result();
        $atencion = $result->fetch_assoc();
        
        error_log("Resultado búsqueda atención: " . ($atencion ? "ENCONTRADA id: " . $atencion['id'] : "NO ENCONTRADA"));
        
        if (!$atencion) {
        } else {
            $id_atencion = $atencion['id'];
            error_log("✅ Usando atención existente id: $id_atencion");
        }
        
        // 2. Guardar servicio de laboratorio
        if ($id > 0) {
            // Actualizar registro existente
            $sql = "UPDATE tbl_atencion_laboratorios 
                    SET id_servicio = ?, notas = ?, fecha = NOW()
                    WHERE id = ? AND id_atencion = ?";
            error_log("Actualizando servicio: $sql");
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isii", $id_servicio, $notas, $id, $id_atencion);
        } else {
            // Insertar nuevo registro
            $sql = "INSERT INTO tbl_atencion_laboratorios 
                    (id_atencion, id_servicio, notas, fecha) 
                    VALUES (?, ?, ?, NOW())";
            error_log("Insertando servicio: $sql");
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $id_atencion, $id_servicio, $notas);
        }
        
        if ($stmt->execute()) {
            $affected_rows = $stmt->affected_rows;
            $insert_id = $conn->insert_id;
            
            error_log("✅ Operación exitosa. Filas afectadas: $affected_rows, Insert ID: $insert_id");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Servicio guardado correctamente',
                'debug' => [
                    'id_atencion' => $id_atencion,
                    'affected_rows' => $affected_rows,
                    'insert_id' => $insert_id,
                    'operation' => ($id > 0 ? 'update' : 'insert')
                ]
            ]);
        } else {
            throw new Exception("Error al guardar servicio: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage(),
            'debug' => [
                'id_cita' => $id_cita,
                'id_servicio' => $id_servicio,
                'error' => $e->getMessage()
            ]
        ]);
    }
}

function eliminarServicio($conn) {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    $sql = "DELETE FROM tbl_atencion_laboratorios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Servicio eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar servicio']);
    }
}

function getServiciosPDF($conn) {
    $id_cita = intval($_POST['id_cita'] ?? 0);
    
    if ($id_cita <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }
    
    // Obtener servicios para PDF
    $sql = "SELECT 
                cs.nombre,
                al.notas
            FROM tbl_atencion_laboratorios al
            INNER JOIN cat_servicio cs ON al.id_servicio = cs.id
            INNER JOIN tbl_atencion a ON al.id_atencion = a.id
            WHERE a.id_cita = ?
            ORDER BY cs.nombre";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cita);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $servicios = [];
    while ($row = $result->fetch_assoc()) {
        $servicios[] = [
            'nombre' => $row['nombre'],
            'notas' => $row['notas']
        ];
    }
    
    echo json_encode(['success' => true, 'servicios' => $servicios]);
}
?>