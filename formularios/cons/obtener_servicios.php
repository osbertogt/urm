<?php
session_start();
include_once '../../assets/dbc.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar que la conexión a la base de datos esté establecida
if (!$conn) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

try {
    // Consulta preparada para obtener todos los servicios activos
    $query = "SELECT id, nombre FROM cat_servicio ORDER BY nombre";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $servicios = [];
    
    while ($row = $result->fetch_assoc()) {
        $servicios[] = [
            'id' => (int)$row['id'],
            'nombre' => htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8')
        ];
    }
    
    // Liberar resultado y cerrar statement
    $stmt->free_result();
    $stmt->close();
    
    // Devolver resultados en formato JSON
    echo json_encode($servicios);
    
} catch (Exception $e) {
    // Manejar errores
    error_log('Error en obtener_servicios.php: ' . $e->getMessage());
    
    // Enviar respuesta de error
    if (headers_sent() === false) {
        header('HTTP/1.1 500 Internal Server Error');
    }
    
    echo json_encode([
        'error' => 'Error al obtener los servicios',
        'message' => $e->getMessage()
    ]);
}

// Cerrar conexión
if ($conn) {
    $conn->close();
}
?>