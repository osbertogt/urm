<?php
// formularios/cons/pdt/curvas_oms_ajax.php
header('Content-Type: application/json');
require_once '../../../../assets/dbc.php';

$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;

if ($id_cliente <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente inválido']);
    exit;
}

// 1. Obtener datos de la vista vw_datos_antro
$sql = "SELECT 
    id_cliente,
    sexo,
    edad_meses,
    talla,
    peso,
    circ_cefalica,
    DATE_FORMAT(fecha, '%Y-%m-%d') as fecha
FROM vw_datos_antro 
WHERE id_cliente = ? 
ORDER BY fecha DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_cliente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$datos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $datos[] = [
        'id_cliente' => intval($row['id_cliente']),
        'sexo' => $row['sexo'],
        'edad_meses' => floatval($row['edad_meses']),
        'talla' => $row['talla'] !== null ? floatval($row['talla']) : null,
        'peso' => $row['peso'] !== null ? floatval($row['peso']) : null,
        'circ_cefalica' => $row['circ_cefalica'] !== null ? floatval($row['circ_cefalica']) : null,
        'fecha' => $row['fecha']
    ];
}

// 2. Obtener información del paciente
$sql_paciente = "SELECT 
    CONCAT(nombre_1, ' ', apellido_1) as nombre,
    fecha_nacimiento,
    CASE id_sexo 
        WHEN 2 THEN 'M' 
        ELSE 'F' 
    END as sexo
FROM tbl_persona 
WHERE id = ?";

$stmt_paciente = mysqli_prepare($conn, $sql_paciente);
mysqli_stmt_bind_param($stmt_paciente, "i", $id_cliente);
mysqli_stmt_execute($stmt_paciente);
$result_paciente = mysqli_stmt_get_result($stmt_paciente);
$paciente = mysqli_fetch_assoc($result_paciente);

// 3. Preparar respuesta
echo json_encode([
    'success' => true,
    'paciente' => $paciente ?: ['nombre' => 'Desconocido', 'sexo' => 'F', 'fecha_nacimiento' => ''],
    'datos' => $datos
]);

mysqli_close($conn);
?>