<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../assets/dbc.php';

$response = [
    'modelo' => 1,
    'rangos' => []
];

if (!isset($_GET['id_medico']) || !is_numeric($_GET['id_medico'])) {
    echo json_encode($response);
    exit;
}

$id_medico = intval($_GET['id_medico']);

// --- Obtener el modelo del médico ---
$sql_modelo = "SELECT modelo FROM tbl_persona WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql_modelo)) {
    mysqli_stmt_bind_param($stmt, 'i', $id_medico);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $modelo);
    if (mysqli_stmt_fetch($stmt)) {
        $response['modelo'] = (int)$modelo;
    }
    mysqli_stmt_close($stmt);
}

// --- Obtener rangos de fechas ---
$sql_rangos = "
    SELECT 
        DATE(fecha_inicio) AS fecha_inicio,
        DATE(fecha_final) AS fecha_final,
        habilitado
    FROM tbl_medico_horario_atencion_fecha
    WHERE id_medico = ?
";
if ($stmt = mysqli_prepare($conn, $sql_rangos)) {
    mysqli_stmt_bind_param($stmt, 'i', $id_medico);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        // Para modelo 1: deshabilitamos los rangos donde habilitado=0
        // Para modelo 2: habilitamos solo los rangos donde habilitado=1
        if (
            ($response['modelo'] == 1 && $row['habilitado'] == 0) ||
            ($response['modelo'] == 2 && $row['habilitado'] == 1)
        ) {
            $response['rangos'][] = [
                'fecha_inicio' => $row['fecha_inicio'],
                'fecha_final' => $row['fecha_final']
            ];
        }
    }
    mysqli_stmt_close($stmt);
}

echo json_encode($response);
exit;
