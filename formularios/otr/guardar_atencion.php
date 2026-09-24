<?php
require_once '../../assets/dbc.php';

// DEBUG: Verificar todo el POST
error_log("=== DEBUG SERVICIOS ===");
error_log("POST completo: " . print_r($_POST, true));
error_log("Servicios recibido: " . print_r($_POST['servicios'] ?? 'NO EXISTE', true));
error_log("Contenido de servicios: " . ($_POST['servicios'] ? implode(', ', $_POST['servicios']) : 'VACIO'));

$id_atencion = $_POST['id_atencion'] ?? null;
$id_medico = $_POST['id_medico'] ?? null;
$id_cliente = $_POST['id_cliente'] ?? null;
$fecha = $_POST['inputFecha'] ?? null;
$fecha_entrega = $_POST['fecha_entrega_resultado'] ?? null;
$servicios = $_POST['servicios'] ?? [];
$id_tipoatencion = 3;
$ototal = $_POST['totalorden'] ?? 0;
$oabono = $_POST['abono'] ?? 0;
$osaldo = $_POST['saldo'] ?? 0;
$stat = isset($_POST['inputStat']) ? 1 : 0;
$id_estado = 1;
if (empty($servicios)) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron servicios']);
    exit;
}
if ($id_atencion) {
    // Actualizar atención existente
    $stmt = $conn->prepare("UPDATE tbl_atencion SET id_medico=?, fecha=?, fecha_entrega_resultado=?, stat=?,
							total=?, abono=?, saldo=?, notas=? WHERE id=?");
    $stmt->bind_param("isssdddsi", $id_medico, $fecha, $fecha_entrega, $stat, $ototal, $oabono, $osaldo, $notas, $id_atencion,);
    $stmt->execute();

	$id_area_destino = 2; 
	$nstmt = $conn->prepare("
		INSERT INTO tbl_notificaciones_orden 
		(id_orden, id_area_destino, titulo, mensaje) 
		VALUES (?, ?, ?, ?)
	");
	$titulo = "Nueva Orden de Servicio";
	$mensaje = "Nueva orden #$id_atencion creada. ";
	$nstmt->bind_param("iiss", $id_atencion, $id_area_destino, $titulo, $mensaje);
	$nstmt->execute();

    // Eliminar servicios y variables anteriores
    $conn->query("DELETE FROM tbl_atencion_servicio WHERE id_atencion = $id_atencion");
    $conn->query("DELETE FROM tbl_atencion_servicio_variable WHERE id_atencion = $id_atencion");
} else {
	$query_max = "SELECT COALESCE(MAX(CAST(numero_ticket AS UNSIGNED)), 0) + 1 as siguiente_ticket 
              FROM tbl_atencion";
	$result = $conn->query($query_max);
	$row = $result->fetch_assoc();
	$nuevoNumeroTicket = $row['siguiente_ticket'];

    $stmt = $conn->prepare("INSERT INTO tbl_atencion (id_tipo_atencion, numero_ticket, fecha, fecha_entrega_resultado, id_medico, stat, id_cliente, id_estado, total, abono, saldo)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssiiiiddd", $id_tipoatencion, $nuevoNumeroTicket, $fecha, $fecha_entrega, $id_medico, $stat, $id_cliente, $id_estado, $ototal, $oabono, $osaldo);
    $stmt->execute();
    $id_atencion = $stmt->insert_id;
	$id_area_destino = 2; 
	$nstmt = $conn->prepare("
		INSERT INTO tbl_notificaciones_orden 
		(id_orden, id_area_destino, titulo, mensaje) 
		VALUES (?, ?, ?, ?)
	");
	$titulo = "Nueva Orden de Servicio";
	$mensaje = "Nueva orden #$id_atencion creada. ";
	$nstmt->bind_param("iiss", $id_atencion, $id_area_destino, $titulo, $mensaje);
	$nstmt->execute();
}

$stmt_servicio = $conn->prepare("INSERT INTO tbl_atencion_servicio (id_atencion, id_servicio) VALUES (?, ?)");
$stmt_variable = $conn->prepare("INSERT INTO tbl_atencion_servicio_variable (id_atencion, id_servicio, id_variable, variable_resultado) VALUES (?, ?, ?, NULL)");

// Insertar servicios y variables asociadas
foreach ($servicios as $id_servicio) {
    // Insertar servicio
    $stmt_servicio->bind_param("ii", $id_atencion, $id_servicio);
    $stmt_servicio->execute();

    // Obtener tipo de análisis
    $stmt_tipo = $conn->prepare("SELECT id_tipo_analisis FROM cat_servicio WHERE id = ?");
    $stmt_tipo->bind_param("i", $id_servicio);
    $stmt_tipo->execute();
    $stmt_tipo->bind_result($id_tipo_analisis);
    $stmt_tipo->fetch();
    $stmt_tipo->close();

    if ($id_tipo_analisis) {
        // Obtener variables de ese tipo de análisis
        $sql_vars = "
            SELECT v.id
            FROM cat_variable v
            WHERE v.id_servicio = ?
        ";
        $stmt_vars = $conn->prepare($sql_vars);
        $stmt_vars->bind_param("i", $id_servicio);
        $stmt_vars->execute();
        $result_vars = $stmt_vars->get_result();

        while ($row = $result_vars->fetch_assoc()) {
            $id_variable = $row['id'];
            $stmt_variable->bind_param("iii", $id_atencion, $id_servicio, $id_variable);
            $stmt_variable->execute();
        }

        $stmt_vars->close();
    }
}

//echo "ok";
echo json_encode(['success' => true]);
?>
