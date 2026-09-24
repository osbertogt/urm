<?php
session_start();
include_once '../../assets/dbc.php';

header('Content-Type: application/json; charset=utf-8');


// Validar y sanitizar datos
$id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
$motivo_consulta = filter_input(INPUT_POST, 'motivo_consulta', FILTER_SANITIZE_SPECIAL_CHARS);
$sintomas = filter_input(INPUT_POST, 'sintomas', FILTER_SANITIZE_SPECIAL_CHARS);
$diagnostico = filter_input(INPUT_POST, 'diagnostico', FILTER_SANITIZE_SPECIAL_CHARS);
$indicaciones_medicas = filter_input(INPUT_POST, 'indicaciones_medicas', FILTER_SANITIZE_SPECIAL_CHARS);
$laboratorios = filter_input(INPUT_POST, 'laboratorios', FILTER_SANITIZE_SPECIAL_CHARS);
$medicamentos_administrados = filter_input(INPUT_POST, 'medicamentos_administrados', FILTER_SANITIZE_SPECIAL_CHARS);
$receta = filter_input(INPUT_POST, 'receta', FILTER_SANITIZE_SPECIAL_CHARS);

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Insertar en tbl_atencion
    $query1 = "INSERT INTO tbl_atencion (fecha, id_cliente) VALUES (NOW(), ?)";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param("i", $id_cliente);
    $stmt1->execute();
    $id_atencion = $conn->insert_id;
    $stmt1->close();

    // 2. Insertar en tbl_atencion_consulta
    $query2 = "INSERT INTO tbl_atencion_consulta (
        id_atencion, motivo_consulta, sintomas, diagnostico, indicaciones_medicas, laboratorios, medicamentos_administrados, receta,
        anam_r_alergias_si, anam_r_alergias_no, anam_r_alergias_obs,
        anam_r_ecronicas_si, anam_r_ecronicas_no, anam_r_ecronicas_obs,
        anam_r_aquirurgi_si, anam_r_aquirurgi_no, anam_r_aquirurgi_obs,
        anam_r_lesiones_si, anam_r_lesiones_no, anam_r_lesiones_obs,
        anam_r_embarazo_si, anam_r_embarazo_no, anam_r_embarazo_obs,
        anam_r_etsvih_si, anam_r_etsvih_no, anam_r_etsvih_obs,
        anam_r_farmacos_si, anam_r_farmacos_no, anam_r_farmacos_obs,
        anam_r_otros_si, anam_r_otros_no, anam_r_otros_obs
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt2 = $conn->prepare($query2);
    
    // Obtener valores de anamnesis
    $valores_anamnesis = [];
    $campos = ['alergias', 'ecronicas', 'aquirurgi', 'lesiones', 'embarazo', 'etsvih', 'farmacos', 'otros'];
    
    foreach ($campos as $campo) {
        $valores_anamnesis[] = isset($_POST["anam_r_{$campo}_si"]) ? 1 : 0;
        $valores_anamnesis[] = isset($_POST["anam_r_{$campo}_no"]) ? 1 : 0;
        $valores_anamnesis[] = filter_input(INPUT_POST, "anam_r_{$campo}_obs", FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
    }
    
    $params = array_merge([
        $id_atencion, 
        $motivo_consulta, 
        $sintomas, 
        $diagnostico, 
        $indicaciones_medicas,
		$laboratorios,
		$medicamentos_administrados,
		$receta
    ], $valores_anamnesis);
    
    // Crear cadena de tipos para bind_param
    $types = 'isssssss' . str_repeat('iis', count($campos));
    
    $stmt2->bind_param($types, ...$params);
    $stmt2->execute();
    $stmt2->close();
    
    $conn->commit();
	$conn->close();
    echo json_encode(['success' => true, 'message' => 'Atención guardada correctamente']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}


?>