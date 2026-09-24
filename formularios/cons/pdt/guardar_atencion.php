<?php

if(!isset($_SESSION)) {session_start();}


include_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id_cliente = intval($_POST['numerocliente']);
$id_medico = intval($_POST['numeromedico']);
$id_cita = intval($_POST['numerocita']);

$id_motivo_admision = filter_input(INPUT_POST, 'id_motivo_admision', FILTER_VALIDATE_INT);
$precio_consulta = filter_input(INPUT_POST, 'precioConsulta', FILTER_VALIDATE_FLOAT);

$datos_parto = filter_input(INPUT_POST, 'datos_parto', FILTER_SANITIZE_SPECIAL_CHARS);
$datos_recien_nacido = filter_input(INPUT_POST, 'datos_recien_nacido', FILTER_SANITIZE_SPECIAL_CHARS);
$alimentacion_1er_anio = filter_input(INPUT_POST, 'alimentacion_1er_anio', FILTER_SANITIZE_SPECIAL_CHARS);
$desarrollo_psicomotor = filter_input(INPUT_POST, 'desarrollo_psicomotor', FILTER_SANITIZE_SPECIAL_CHARS);
$motivo_consulta = filter_input(INPUT_POST, 'motivo_consulta', FILTER_SANITIZE_SPECIAL_CHARS);
$historia_enfermedad_actual = filter_input(INPUT_POST, 'historia_enfermedad_actual', FILTER_SANITIZE_SPECIAL_CHARS);
$examen_fisico = filter_input(INPUT_POST, 'examen_fisico', FILTER_SANITIZE_SPECIAL_CHARS);
$sv_temperatura = filter_input(INPUT_POST, 'sv_temperatura', FILTER_SANITIZE_SPECIAL_CHARS);
$sv_frecuencia_cardiaca = filter_input(INPUT_POST, 'sv_frecuencia_cardiaca', FILTER_SANITIZE_SPECIAL_CHARS);
$sv_frecuencia_respiratoria = filter_input(INPUT_POST, 'sv_frecuencia_respiratoria', FILTER_SANITIZE_SPECIAL_CHARS);
$sv_presion_arterial = filter_input(INPUT_POST, 'sv_presion_arterial', FILTER_SANITIZE_SPECIAL_CHARS);
$talla = filter_input(INPUT_POST, 'talla', FILTER_SANITIZE_SPECIAL_CHARS);
$peso = filter_input(INPUT_POST, 'peso', FILTER_SANITIZE_SPECIAL_CHARS);
$circ_cefalica = filter_input(INPUT_POST, 'circ_cefalica', FILTER_SANITIZE_SPECIAL_CHARS);
$diagnostico = filter_input(INPUT_POST, 'diagnostico', FILTER_SANITIZE_SPECIAL_CHARS);
$tratamiento = filter_input(INPUT_POST, 'tratamiento', FILTER_SANITIZE_SPECIAL_CHARS);
$medicamentos_administrados = filter_input(INPUT_POST, 'medicamentos_administrados', FILTER_SANITIZE_SPECIAL_CHARS);
$receta = filter_input(INPUT_POST, 'receta', FILTER_SANITIZE_SPECIAL_CHARS);
$laboratorios = filter_input(INPUT_POST, 'laboratorios', FILTER_SANITIZE_SPECIAL_CHARS);
$proxima_cita = filter_input(INPUT_POST, 'proxima_cita', FILTER_VALIDATE_INT);

$id_tipo_atencion = 3;
$id_especialidad = 1;
$id_servicio = ($id_motivo_admision == 1) ? 32 : 33;
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
                        SET id_cliente = ?, id_medico = ?, id_especialidad = ?, id_tipo_atencion = ?, id_motivo_admision = ?, total = ?, fecha = NOW(), fecha_proxima_cita = ?
                        WHERE id = ?";
        $stmt1 = $conn->prepare($sql_update1);
        $stmt1->bind_param("iiiiidis", $id_cliente, $id_medico, $id_especialidad, $id_tipo_atencion, $id_motivo_admision, $precio_consulta, $id_atencion, $fecha_proxima_cita);
        $stmt1->execute();
        $stmt1->close();

        //  2. Actualizar tbl_atencion_consulta
        $sql_update2 = "UPDATE tbl_atencion_consulta
                        SET 
						datos_parto = ?,
						datos_recien_nacido = ?,
						alimentacion_1er_anio = ?,
						desarrollo_psicomotor = ?,
						motivo_consulta = ?,
						historia_enfermedad_actual = ?,
						examen_fisico = ?,
						sv_temperatura = ?,
						sv_frecuencia_cardiaca = ?,
						sv_frecuencia_respiratoria = ?,
						sv_presion_arterial = ?,
						talla = ?,
						peso = ?,
						circ_cefalica = ?,
						diagnostico = ?,
						tratamiento = ?,
						medicamentos_administrados = ?,
						receta = ?,
						laboratorios = ?		
						WHERE id_atencion = ?";
        $stmt2 = $conn->prepare($sql_update2);
        $stmt2->bind_param("sssssssiiisdddsssssi", 
		$datos_parto,
		$datos_recien_nacido,
		$alimentacion_1er_anio,
		$desarrollo_psicomotor,
		$motivo_consulta,
		$historia_enfermedad_actual,
		$examen_fisico,
		$sv_temperatura,
		$sv_frecuencia_cardiaca,
		$sv_frecuencia_respiratoria,
		$sv_presion_arterial,
		$talla,
		$peso,
		$circ_cefalica,
		$diagnostico,
		$tratamiento,
		$medicamentos_administrados,
		$receta,
		$laboratorios,
		$id_atencion);
        $stmt2->execute();
        $stmt2->close();

        //  3. Actualizar o insertar tbl_atencion_servicio
        $sql_check_serv = "SELECT id FROM tbl_atencion_servicio WHERE id_atencion = ?";
        $stmt_serv = $conn->prepare($sql_check_serv);
        $stmt_serv->bind_param("i", $id_atencion);
        $stmt_serv->execute();
        $stmt_serv->store_result();

        if ($stmt_serv->num_rows > 0) {
            $sql_update3 = "UPDATE tbl_atencion_servicio SET id_servicio = ?, precio = ? WHERE id_atencion = ?";
            $stmt3 = $conn->prepare($sql_update3);
            $stmt3->bind_param("idi", $id_servicio, $precio_consulta, $id_atencion);
            $stmt3->execute();
            $stmt3->close();
        } else {
            $sql_insert3 = "INSERT INTO tbl_atencion_servicio (id_atencion, id_servicio, precio) VALUES (?, ?, ?)";
            $stmt3 = $conn->prepare($sql_insert3);
            $stmt3->bind_param("iid", $id_atencion, $id_servicio, $precio_consulta);
            $stmt3->execute();
            $stmt3->close();
        }

        $stmt_serv->close();
        $accion = 'actualizada';

    } else {
        // No existe: crear nueva atención

        // Obtener número de ticket siguiente
        $query_max = "SELECT COALESCE(MAX(CAST(numero_ticket AS UNSIGNED)), 0) + 1 AS siguiente_ticket FROM tbl_atencion";
        $result = $conn->query($query_max);
        $row = $result->fetch_assoc();
        $nuevoNumeroTicket = $row['siguiente_ticket'];

        // 1. Insertar en tbl_atencion
        $query1 = "INSERT INTO tbl_atencion (fecha, id_cita, numero_ticket, id_cliente, id_medico, id_especialidad, id_tipo_atencion, id_motivo_admision, total, fecha_proxima_cita)
                   VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("isiiiiids", $id_cita, $nuevoNumeroTicket, $id_cliente, $id_medico, $id_especialidad, $id_tipo_atencion, $id_motivo_admision, $precio_consulta, $fecha_proxima_cita);
        $stmt1->execute();
        $id_atencion = $conn->insert_id;
        $stmt1->close();

        // 2. Insertar en tbl_atencion_consulta
        $query2 = "INSERT INTO tbl_atencion_consulta 
		   (id_atencion,
		    id_tipo_atencion,
			datos_parto,
			datos_recien_nacido,
			alimentacion_1er_anio,
			desarrollo_psicomotor,
			motivo_consulta,
			historia_enfermedad_actual,
			examen_fisico,
			sv_temperatura,
			sv_frecuencia_cardiaca,
			sv_frecuencia_respiratoria,
			sv_presion_arterial,
			talla,
			peso,
			circ_cefalica,
			diagnostico,
			tratamiento,
			medicamentos_administrados,
			receta,
			laboratorios)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("iisssssssiiisdddsssss", 
		$id_atencion, 
		$id_tipo_atencion, 
		$datos_parto,
		$datos_recien_nacido,
		$alimentacion_1er_anio,
		$desarrollo_psicomotor,
		$motivo_consulta,
		$historia_enfermedad_actual,
		$examen_fisico,
		$sv_temperatura,
		$sv_frecuencia_cardiaca,
		$sv_frecuencia_respiratoria,
		$sv_presion_arterial,
		$talla,
		$peso,
		$circ_cefalica,
		$diagnostico,
		$tratamiento,
		$medicamentos_administrados,
		$receta,
		$laboratorios);
        $stmt2->execute();
        $stmt2->close();

        // 3. Insertar en tbl_atencion_servicio
        $quer3 = "INSERT INTO tbl_atencion_servicio (id_atencion, id_servicio, precio) VALUES (?, ?, ?)";
        $stmt3 = $conn->prepare($quer3);
        $stmt3->bind_param("iid", $id_atencion, $id_servicio, $precio_consulta);
        $stmt3->execute();
        $stmt3->close();

        $accion = 'registrada';
    }

    $stmt_check->close();

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
