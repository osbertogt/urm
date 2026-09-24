<?php
if(!isset($_SESSION)) {session_start();}

include_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id_cliente = intval($_POST['id_cliente']);
$id_medico = intval($_POST['id_medico']);
$id_cita = intval($_POST['id_cita']);

$id_motivo_admision = filter_input(INPUT_POST, 'id_motivo_admision', FILTER_VALIDATE_INT);
$precio_consulta = filter_input(INPUT_POST, 'precioConsulta', FILTER_VALIDATE_FLOAT);
$motivo_consulta = filter_input(INPUT_POST, 'motivo_consulta', FILTER_SANITIZE_SPECIAL_CHARS);
$sintomas = filter_input(INPUT_POST, 'sintomas', FILTER_SANITIZE_SPECIAL_CHARS);
$examen_fisico = filter_input(INPUT_POST, 'examen_fisico', FILTER_SANITIZE_SPECIAL_CHARS);
$diagnostico = filter_input(INPUT_POST, 'diagnostico', FILTER_SANITIZE_SPECIAL_CHARS);
$indicaciones_medicas = filter_input(INPUT_POST, 'indicaciones_medicas', FILTER_SANITIZE_SPECIAL_CHARS);
$laboratorios = filter_input(INPUT_POST, 'laboratorios', FILTER_SANITIZE_SPECIAL_CHARS);
$medicamentos_administrados = filter_input(INPUT_POST, 'medicamentos_administrados', FILTER_SANITIZE_SPECIAL_CHARS);
$receta = filter_input(INPUT_POST, 'receta', FILTER_SANITIZE_SPECIAL_CHARS);

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
                        SET id_cliente = ?, id_medico = ?, id_especialidad = ?, id_tipo_atencion = ?, id_motivo_admision = ?, total = ?, fecha = NOW()
                        WHERE id = ?";
        $stmt1 = $conn->prepare($sql_update1);
        $stmt1->bind_param("iiiiidi", $id_cliente, $id_medico, $id_especialidad, $id_tipo_atencion, $id_motivo_admision, $precio_consulta, $id_atencion);
        $stmt1->execute();
        $stmt1->close();

        //  2. Actualizar tbl_atencion_consulta
        $sql_update2 = "UPDATE tbl_atencion_consulta
                        SET motivo_consulta = ?, sintomas = ?, diagnostico = ?, indicaciones_medicas = ?, laboratorios = ?, medicamentos_administrados = ?, receta = ?
                        WHERE id_atencion = ?";
        $stmt2 = $conn->prepare($sql_update2);
        $stmt2->bind_param("sssssssi", $motivo_consulta, $sintomas, $diagnostico, $indicaciones_medicas, $laboratorios, $medicamentos_administrados, $receta, $id_atencion);
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
        $query1 = "INSERT INTO tbl_atencion (fecha, id_cita, numero_ticket, id_cliente, id_medico, id_especialidad, id_tipo_atencion, id_motivo_admision, total)
                   VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("isiiiiid", $id_cita, $nuevoNumeroTicket, $id_cliente, $id_medico, $id_especialidad, $id_tipo_atencion, $id_motivo_admision, $precio_consulta);
        $stmt1->execute();
        $id_atencion = $conn->insert_id;
        $stmt1->close();

        // 2. Insertar en tbl_atencion_consulta
        $query2 = "INSERT INTO tbl_atencion_consulta (id_atencion, id_tipo_atencion, motivo_consulta, sintomas, diagnostico, indicaciones_medicas, laboratorios, medicamentos_administrados, receta)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("iisssssss", $id_atencion, $id_tipo_atencion, $motivo_consulta, $sintomas, $diagnostico, $indicaciones_medicas, $laboratorios, $medicamentos_administrados, $receta);
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
