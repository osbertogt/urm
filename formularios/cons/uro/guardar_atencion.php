<?php
include_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

if(!isset($_SESSION)) {session_start();}
$servicios = $_POST['id_servicio'] ?? [];

if (!is_array($servicios)) {
    $servicios = [];
}


$id_cliente = intval($_POST['id_cliente']);
$id_medico = intval($_POST['id_medico']);
$id_cita = intval($_POST['id_cita']);

$id_motivo_admision = intval($_POST['id_motivo_admision']);
$precio_consulta = $_POST['precioConsulta'];
$motivo_consulta = $_POST['motivo_consulta'];
$sintomas = '';
$examen_fisico = $_POST['examen_fisico'];
$diagnostico = $_POST['diagnostico'];
$indicaciones_medicas = $_POST['indicaciones_medicas'];
$laboratorios = $_POST['laboratorios'];
$laboratorios2 = $_POST['laboratorios2'];
$laboratorios3 = $_POST['laboratorios3'];
$medicamentos_administrados = $_POST['medicamentos_administrados'];
$receta = $_POST['receta'];
$proxima_cita = $_POST['proxima_cita'];
$historia_enfermedad = $_POST['historia_enfermedad'];

$id_tipo_atencion = 3;
$id_especialidad = 1;
$id_servicio = 32;
$id_estado_atendida = 7; 

// Iniciar transacción
$conn->begin_transaction();

try {
    // Verificar si la cita ya tiene una atención
    $sql_check = "SELECT id FROM tbl_atencion WHERE id_cita = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_cita);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Ya existe: obtener id_atencion
        $stmt_check->bind_result($id_atencion);
        $stmt_check->fetch();

        // 1. Actualizar tbl_atencion
        $sql_update1 = "UPDATE tbl_atencion 
                        SET id_cliente = ?, id_medico = ?, id_especialidad = ?, id_tipo_atencion = ?, id_motivo_admision = ?, 
						total = ?, fecha = NOW()
                        WHERE id = ?";
        $stmt1 = $conn->prepare($sql_update1);
        $stmt1->bind_param("iiiiidi", $id_cliente, $id_medico, $id_especialidad, $id_tipo_atencion, $id_motivo_admision, 
		$precio_consulta, $id_atencion);
        $stmt1->execute();
        $stmt1->close();

        //  2. Actualizar tbl_atencion_consulta
        $sql_update2 = "UPDATE tbl_atencion_consulta
                        SET motivo_consulta = ?, historia_enfermedad_actual = ?, diagnostico = ?, indicaciones_medicas = ?, 
						laboratorios = ?,laboratorios2 = ?,laboratorios3 = ?, 
						medicamentos_administrados = ?, examen_fisico = ?,
						receta = ?, fecha_proxima_cita = ?
                        WHERE id_atencion = ?";
        $stmt2 = $conn->prepare($sql_update2);
        $stmt2->bind_param("sssssssssssi", $motivo_consulta, $historia_enfermedad, $diagnostico, $indicaciones_medicas, 
		$laboratorios,$laboratorios2,$laboratorios3, 
		$medicamentos_administrados, $examen_fisico, $receta, $proxima_cita, $id_atencion);
        $stmt2->execute();
        $stmt2->close();

        $accion = 'actualizada';

    } else {
        // No existe: crear nueva atención

        // Obtener número de ticket siguiente
        $query_max = "SELECT COALESCE(MAX(CAST(numero_ticket AS UNSIGNED)), 0) + 1 AS siguiente_ticket FROM tbl_atencion";
        $result = $conn->query($query_max);
        $row = $result->fetch_assoc();
        $nuevoNumeroTicket = $row['siguiente_ticket'];

        // 1. Insertar en tbl_atencion
        $query1 = "INSERT INTO tbl_atencion (fecha, id_cita, numero_ticket, id_cliente, id_medico, id_especialidad, id_tipo_atencion, id_motivo_admision, total)
                   VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("isiiiiid", $id_cita, $nuevoNumeroTicket, $id_cliente, $id_medico, $id_especialidad, $id_tipo_atencion, $id_motivo_admision, $precio_consulta);
        $stmt1->execute();
        $id_atencion = $conn->insert_id;
        $stmt1->close();

        // 2. Insertar en tbl_atencion_consulta
        $query2 = "INSERT INTO tbl_atencion_consulta (id_atencion, id_tipo_atencion, motivo_consulta, historia_enfermedad_actual, 
				   diagnostico, indicaciones_medicas, laboratorios, laboratorios2, laboratorios3, medicamentos_administrados, receta, fecha_proxima_cita)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("iissssssssss", $id_atencion, $id_tipo_atencion, $motivo_consulta, $historia_enfermedad, $diagnostico, 
		$indicaciones_medicas, $laboratorios, $laboratorios2, $laboratorios3, 
		$medicamentos_administrados, $receta, $fecha_proxima_cita);
        $stmt2->execute();
        $stmt2->close();


        $accion = 'registrada';
    }

/* 	$sql_delete = "DELETE FROM tbl_atencion_servicio WHERE id_atencion = ?";
	$stmt_del = $conn->prepare($sql_delete);
	$stmt_del->bind_param("i", $id_atencion);
	$stmt_del->execute();
	$stmt_del->close();

	$sql_insert_serv = "INSERT INTO tbl_atencion_servicio (id_atencion, id_servicio, precio) VALUES (?, ?, ?)";
	$stmt_serv = $conn->prepare($sql_insert_serv);
	$stmt_serv->bind_param("iid", $id_atencion, $id_servicio, $precio_consulta);
	$stmt_serv->execute();
 */
   // GUARDAR ANTECEDENTES

	if (isset($_POST['datos_antecedentes_json'])) {
		$antecedentes_json = json_decode($_POST['datos_antecedentes_json'], true);
		
		if (is_array($antecedentes_json) && $id_cliente > 0) {
			foreach ($antecedentes_json as $antecedente) {
				$sql_ant = "INSERT INTO tbl_atencion_consulta_antecedentes 
						   (id_cliente, id_especialidad, id_antecedente, presente, notas) 
						   VALUES (?, ?, ?, ?, ?)
						   ON DUPLICATE KEY UPDATE 
						   presente = VALUES(presente), 
						   notas = VALUES(notas)
						   ";
				
				$stmt_ant = $conn->prepare($sql_ant);
				$stmt_ant->bind_param("iisis", 
					$id_cliente,
					$id_especialidad,
					$antecedente['numero'],
					$antecedente['presente'],
					$antecedente['notas']
				);
				$stmt_ant->execute();
				$stmt_ant->close();
			}
		}
	}


	
    $sql_update_estado = "UPDATE tbl_cita SET id_estado = ? WHERE id = ?";
    $stmt_estado = $conn->prepare($sql_update_estado);
    $stmt_estado->bind_param("ii", $id_estado_atendida, $id_cita);
    $stmt_estado->execute();
    $stmt_estado->close();

    $conn->commit();
    $conn->close();

    echo json_encode(['success' => true, 'message' => "Atención $accion correctamente"]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
