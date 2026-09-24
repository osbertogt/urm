<?php
require_once '../../../assets/dbc.php';

header('Content-Type: application/json');

// Validar parámetros
$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
$id_especialidad = 1;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action !== 'get_antecedentes' || $id_cliente <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Parámetros inválidos',
        'data' => []
    ]);
    exit;
}

try {
    // Consultar antecedentes existentes
    $sql = "SELECT 
                id_antecedente,
                presente,
                notas
            FROM tbl_atencion_consulta_antecedentes 
            WHERE id_cliente = ? 
            AND id_especialidad = ?
            ORDER BY id_antecedente";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_cliente, $id_especialidad);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $antecedentes = [];
    while ($row = $result->fetch_assoc()) {
        $antecedentes[] = [
            'id_antecedente' => intval($row['id_antecedente']),
            'presente' => intval($row['presente']),
            'notas' => htmlspecialchars_decode($row['notas'] ?? '', ENT_QUOTES)
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => count($antecedentes) . ' antecedente(s) encontrados',
        'data' => $antecedentes,
        'count' => count($antecedentes)
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_antecedentes: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage(),
        'data' => []
    ]);
}

$conn->close();
?>