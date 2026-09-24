<?php

if (!isset($_SERVER['REQUEST_METHOD'])) exit;
header('Content-Type: application/json; charset=utf-8');

if(!isset($_GET) && !isset($_POST)){
    echo json_encode(['success'=>false,'message'=>'No request data']); exit;
}


$id_cita = isset($_REQUEST['id_cita']) ? intval($_REQUEST['id_cita']) : 0;
if ($id_cita <= 0) {
    echo json_encode(['success'=>false,'message'=>'id_cita inválido']); exit;
}

require_once '../../../assets/dbc.php'; // ajustar ruta si es necesario


try {
    // 1) obtener id_atencion (la más reciente si hay varias)
    $sqlAt = "SELECT id FROM tbl_atencion WHERE id_cita = ? ORDER BY id DESC LIMIT 1";
    $stmtAt = $conn->prepare($sqlAt);
    $stmtAt->bind_param("i", $id_cita);
    $stmtAt->execute();
    $resAt = $stmtAt->get_result();
    $rowAt = $resAt->fetch_assoc();
    $stmtAt->close();

    if (!$rowAt) {
        echo json_encode(['success'=>false,'message'=>'No existe registro de tbl_atencion para esa cita']); exit;
    }

    $id_atencion = (int)$rowAt['id'];

    // 2) obtener la fila más reciente en tbl_atencion_consulta
    $sql = "SELECT id AS id_consulta,
                   fecha_hora,
                   datos_parto,
                   datos_recien_nacido,
                   alimentacion_1er_anio,
                   desarrollo_psicomotor,
                   motivo_consulta,
                   historia_enfermedad_actual,
                   talla,
                   peso,
                   circ_cefalica,
                   examen_fisico,
                   diagnostico,
                   tratamiento,
                   medicamentos_administrados,
                   receta,
                   laboratorios,
                   sintomas,
                   indicaciones_medicas,
                   sv_temperatura,
                   sv_frecuencia_cardiaca,
                   sv_frecuencia_respiratoria,
                   sv_presion_arterial
            FROM tbl_atencion_consulta
            WHERE id_atencion = ?
            ORDER BY id DESC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_atencion);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) {
        // No hay registro de consulta, devolver success true pero data vacío (o success false según prefieras)
        echo json_encode(['success'=>true,'data'=>null]);
        exit;
    }

    // Normalizar a valores simples (evitar recursos del DB en JS)
    $data = [
        'id_consulta' => (int)($row['id_consulta'] ?? 0),
        'fecha_hora' => $row['fecha_hora'] ?? null,
        'datos_parto' => $row['datos_parto'],
        'datos_recien_nacido' => $row['datos_recien_nacido'] ?? null,
        'alimentacion_1er_anio' => $row['alimentacion_1er_anio'] ?? null,
        'desarrollo_psicomotor' => $row['desarrollo_psicomotor'] ?? null,
        'motivo_consulta' => $row['motivo_consulta'] ?? null,
        'historia_enfermedad_actual' => $row['historia_enfermedad_actual'] ?? null,
        'talla' => $row['talla'] !== null ? (float)$row['talla'] : null,
        'peso' => $row['peso'] !== null ? (float)$row['peso'] : null,
        'circ_cefalica' => $row['circ_cefalica'] !== null ? (float)$row['circ_cefalica'] : null,
        'examen_fisico' => $row['examen_fisico'] ?? null,
        'diagnostico' => $row['diagnostico'] ?? null,
        'tratamiento' => $row['tratamiento'] ?? null,
        'medicamentos_administrados' => $row['medicamentos_administrados'] ?? null,
        'receta' => $row['receta'] ?? null,
        'laboratorios' => $row['laboratorios'] ?? null,
        'sintomas' => $row['sintomas'] ?? null,
        'indicaciones_medicas' => $row['indicaciones_medicas'] ?? null,
        'sv_temperatura' => $row['sv_temperatura'] ?? null,
        'sv_frecuencia_cardiaca' => $row['sv_frecuencia_cardiaca'] ?? null,
        'sv_frecuencia_respiratoria' => $row['sv_frecuencia_respiratoria'] ?? null,
        'sv_presion_arterial' => $row['sv_presion_arterial'] ?? null
    ];

    echo json_encode(['success'=>true,'data'=>$data]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Error interno']);
    exit;
}
