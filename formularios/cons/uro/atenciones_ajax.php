<?php
// atenciones_ajax.php
session_start();
require_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';

function out($arr){ echo json_encode($arr); exit; }

/** LIST: devuelve atenciones con paciente y motivo */
if ($action === 'list') {
    $sql = "SELECT a.id, a.fecha, a.numero_ticket,
                   CONCAT(p.nombre_1,' ',COALESCE(p.nombre_2,''),' ',COALESCE(p.apellido_1,'')) AS paciente,
                   s.nombre AS sexo, m.nombre AS motivo
            FROM tbl_atencion a
            JOIN tbl_persona p ON p.id = a.id_cliente
            LEFT JOIN cat_sexo s ON s.id = p.id_sexo
            LEFT JOIN cat_motivo_admision m ON m.id = a.id_motivo_admision
			WHERE a.id_especialidad=2
            ORDER BY a.fecha DESC";
    $res = mysqli_query($conn, $sql);
    $out = [];
    while($r = mysqli_fetch_assoc($res)) {
        $out[] = $r;
    }
    out($out);
}

/** obtener motivos (cat_motivo_admision) */
if ($action === 'get_motivos') {
    $res = mysqli_query($conn, "SELECT id,nombre FROM cat_motivo_admision ORDER BY nombre");
    $a = [];
    while($r = mysqli_fetch_assoc($res)) $a[] = $r;
    out($a);
}

/** obtener lista de antecedentes para una especialidad (id_especialidad=2) */
if ($action === 'get_antecedentes') {
    $sql = "SELECT id,nombre FROM cat_antecedentes WHERE id_especialidad = 2 ORDER BY id";
    $res = mysqli_query($conn, $sql);
    $a = [];
    while($r = mysqli_fetch_assoc($res)) $a[] = $r;
    out($a);
}

/** get_first_consulta: buscar la primera (más antigua) consulta para paciente */
if ($action === 'get_first_consulta') {
    $id_pac = intval($_REQUEST['id_paciente'] ?? 0);
    if (!$id_pac) out(['exists'=>false]);
    $sql = "SELECT c.datos_parto, c.datos_recien_nacido, c.alimentacion_1er_anio, c.desarrollo_psicomotor
            FROM tbl_atencion_consulta c
            JOIN tbl_atencion a ON a.id = c.id_atencion
            WHERE a.id_cliente = ?
            ORDER BY a.fecha ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_pac);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        out(array_merge(['exists'=>true], $row));
    } else out(['exists'=>false]);
}

/** GET: obtener atencion + consulta + antecedentes (para editar) */
if ($action === 'get') {
    $id = intval($_POST['id'] ?? 0);
    if(!$id) out(['success'=>false,'message'=>'ID inválido']);
    // atencion + consulta
    $sql = "SELECT a.id, a.fecha, a.numero_ticket, a.id_motivo_admision,
                   c.* 
            FROM tbl_atencion a
            LEFT JOIN tbl_atencion_consulta c ON c.id_atencion = a.id
            WHERE a.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    if (!$row) out(['success'=>false,'message'=>'No encontrado']);

    // antecedentes
    $sql2 = "SELECT id_antecedente, valor, observaciones FROM tbl_atencion_consulta_antecedentes WHERE id_atencion = ?";
    $st2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($st2, "i", $id);
    mysqli_stmt_execute($st2);
    $r2 = mysqli_stmt_get_result($st2);
    $ant = [];
    while($a = mysqli_fetch_assoc($r2)) $ant[] = $a;

    out(['success'=>true,'data'=>$row,'antecedentes'=>$ant]);
}

/** DELETE atencion */
if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) out(['success'=>false,'message'=>'ID inválido']);
    // borrar en cascade: primero consulta, antecedentes
    $conn->begin_transaction();
    try {
        $st = mysqli_prepare($conn, "DELETE FROM tbl_atencion_consulta_antecedentes WHERE id_atencion = ?");
        mysqli_stmt_bind_param($st, "i", $id); mysqli_stmt_execute($st);
        $st = mysqli_prepare($conn, "DELETE FROM tbl_atencion_consulta WHERE id_atencion = ?");
        mysqli_stmt_bind_param($st, "i", $id); mysqli_stmt_execute($st);
        $st = mysqli_prepare($conn, "DELETE FROM tbl_atencion WHERE id = ?");
        mysqli_stmt_bind_param($st, "i", $id); mysqli_stmt_execute($st);
        $conn->commit();
        out(['success'=>true]);
    } catch(Exception $e){
        $conn->rollback();
        out(['success'=>false,'message'=>$e->getMessage()]);
    }
}

/** ADD / EDIT */
if ($action === 'add' || $action === 'edit') {
    // recogida segura de campos (limitar longitudes)
    $id = intval($_POST['id'] ?? 0);
    $id_paciente = intval($_POST['id_paciente'] ?? 0);
    if (!$id_paciente) out(['success'=>false,'message'=>'Paciente inválido']);

    $id_motivo_admision = intval($_POST['id_motivo_admision'] ?? 0);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    // Primera consulta data
    $datos_parto = substr(trim($_POST['datos_parto'] ?? ''),0,2000);
    $datos_recien_nacido = substr(trim($_POST['datos_recien_nacido'] ?? ''),0,2000);
    $alimentacion_1er_anio = substr(trim($_POST['alimentacion_1er_anio'] ?? ''),0,2000);
    $desarrollo_psicomotor = substr(trim($_POST['desarrollo_psicomotor'] ?? ''),0,2000);

    // Datos atención
    $motivo_consulta = substr(trim($_POST['motivo_consulta'] ?? ''),0,4000);
    $historia_enfermedad_actual = substr(trim($_POST['historia_enfermedad_actual'] ?? ''),0,4000);
    $examen_fisico = substr(trim($_POST['examen_fisico'] ?? ''),0,4000);
    $sv_temperatura = substr(trim($_POST['sv_temperatura'] ?? ''),0,50);
    $sv_frecuencia_cardiaca = substr(trim($_POST['sv_frecuencia_cardiaca'] ?? ''),0,50);
    $sv_frecuencia_respiratoria = substr(trim($_POST['sv_frecuencia_respiratoria'] ?? ''),0,50);
    $sv_presion_arterial = substr(trim($_POST['sv_presion_arterial'] ?? ''),0,50);
    $sv_ausc_pulmonar = substr(trim($_POST['sv_ausc_pulmonar'] ?? ''),0,200);

    $talla = substr(trim($_POST['talla'] ?? ''),0,50);
    $peso = substr(trim($_POST['peso'] ?? ''),0,50);
    $circ_cefalica = substr(trim($_POST['circ_cefalica'] ?? ''),0,50);

    $diagnostico = substr(trim($_POST['diagnostico'] ?? ''),0,4000);
    $tratamiento = substr(trim($_POST['tratamiento'] ?? ''),0,4000);
    $medicamentos_administrados = substr(trim($_POST['medicamentos_administrados'] ?? ''),0,4000);
    $receta = substr(trim($_POST['receta'] ?? ''),0,4000);
    $laboratorio = substr(trim($_POST['laboratorio'] ?? ''),0,4000);

    // iniciar transaccion
    mysqli_begin_transaction($conn);
    try {
        if ($action === 'add') {
            // insertar en tbl_atencion (numero_ticket se genera despues con el id)
            $st = mysqli_prepare($conn, "INSERT INTO tbl_atencion (numero_ticket, id_cliente, fecha, id_motivo_admision, id_medico, id_especialidad) VALUES (?, ?, ?, ?, NULL, NULL)");
            $dummy_ticket = ''; // se actualiza luego
            mysqli_stmt_bind_param($st, "sisii", $dummy_ticket, $id_paciente, $fecha, $id_motivo_admision);
            mysqli_stmt_execute($st);
            $new_id = mysqli_insert_id($conn);
            // generar numero_ticket con padding 7 ceros
            $ticket = str_pad((string)$new_id, 7, '0', STR_PAD_LEFT);
            $st2 = mysqli_prepare($conn, "UPDATE tbl_atencion SET numero_ticket = ? WHERE id = ?");
            mysqli_stmt_bind_param($st2, "si", $ticket, $new_id);
            mysqli_stmt_execute($st2);
            $id_atencion = $new_id;
        } else {
            // editar
            $id_atencion = $id;
            $st = mysqli_prepare($conn, "UPDATE tbl_atencion SET id_motivo_admision = ?, fecha = ? WHERE id = ?");
            mysqli_stmt_bind_param($st, "isi", $id_motivo_admision, $fecha, $id_atencion);
            mysqli_stmt_execute($st);
            // eliminar antecedentes previos para reinsertar
            mysqli_prepare($conn, "DELETE FROM tbl_atencion_consulta_antecedentes WHERE id_atencion = ?") && mysqli_stmt_execute(mysqli_prepare($conn, "DELETE FROM tbl_atencion_consulta_antecedentes WHERE id_atencion = ?"));
        }

        // insertar o actualizar tbl_atencion_consulta
        // si exista registro para id_atencion => update else insert
        $stmtCheck = mysqli_prepare($conn, "SELECT id FROM tbl_atencion_consulta WHERE id_atencion = ?");
        mysqli_stmt_bind_param($stmtCheck, "i", $id_atencion);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        if ($row = mysqli_fetch_assoc($resCheck)) {
            // update
            $id_consulta = $row['id'];
            $up = "UPDATE tbl_atencion_consulta SET datos_parto=?, datos_recien_nacido=?, alimentacion_1er_anio=?, desarrollo_psicomotor=?, motivo_consulta=?, historia_enfermedad_actual=?, examen_fisico=?, sv_temperatura=?, sv_frecuencia_cardiaca=?, sv_frecuencia_respiratoria=?, sv_presion_arterial=?, sv_ausc_pulmonar=?, talla=?, peso=?, circ_cefalica=?, diagnostico=?, tratamiento=?, medicamentos_administrados=?, receta=?, laboratorio=? WHERE id_atencion=?";
            $stU = mysqli_prepare($conn, $up);
            mysqli_stmt_bind_param($stU, str_repeat("s",20)."i",
                $datos_parto,$datos_recien_nacido,$alimentacion_1er_anio,$desarrollo_psicomotor,
                $motivo_consulta,$historia_enfermedad_actual,$examen_fisico,
                $sv_temperatura,$sv_frecuencia_cardiaca,$sv_frecuencia_respiratoria,$sv_presion_arterial,$sv_ausc_pulmonar,
                $talla,$peso,$circ_cefalica,
                $diagnostico,$tratamiento,$medicamentos_administrados,$receta,$laboratorio,
                $id_atencion
            );
            mysqli_stmt_execute($stU);
        } else {
            // insert
            $ins = "INSERT INTO tbl_atencion_consulta (id_atencion, datos_parto, datos_recien_nacido, alimentacion_1er_anio, desarrollo_psicomotor, motivo_consulta, historia_enfermedad_actual, examen_fisico, sv_temperatura, sv_frecuencia_cardiaca, sv_frecuencia_respiratoria, sv_presion_arterial, sv_ausc_pulmonar, talla, peso, circ_cefalica, diagnostico, tratamiento, medicamentos_administrados, receta, laboratorio)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stI = mysqli_prepare($conn, $ins);
            mysqli_stmt_bind_param($stI, "isssssssssssssssissss",
                $id_atencion,$datos_parto,$datos_recien_nacido,$alimentacion_1er_anio,$desarrollo_psicomotor,
                $motivo_consulta,$historia_enfermedad_actual,$examen_fisico,
                $sv_temperatura,$sv_frecuencia_cardiaca,$sv_frecuencia_respiratoria,$sv_presion_arterial,$sv_ausc_pulmonar,
                $talla,$peso,$circ_cefalica,
                $diagnostico,$tratamiento,$medicamentos_administrados,$receta,$laboratorio
            );
            mysqli_stmt_execute($stI);
            $id_consulta = mysqli_insert_id($conn);
        }

        // insertar antecedentes: busco cat_antecedentes id_especialidad=2 para iterar
        $resAnt = mysqli_query($conn, "SELECT id FROM cat_antecedentes WHERE id_especialidad = 2");
        while($r = mysqli_fetch_assoc($resAnt)){
            $aid = (int)$r['id'];
            $si = isset($_POST["ant_{$aid}_si"]) ? 1 : 0;
            $no = isset($_POST["ant_{$aid}_no"]) ? 1 : 0;
            // si ambos desmarcados dejamos sin registro, si ambos marcados priorizamos 'si'
            $valor = $si ? 1 : ($no ? 0 : null);
            $obs = substr(trim($_POST["ant_{$aid}_obs"] ?? ''),0,1000);
            if ($valor === null && $obs === '') continue;
            $stAnt = mysqli_prepare($conn, "INSERT INTO tbl_atencion_consulta_antecedentes (id_atencion, id_antecedente, valor, observaciones) VALUES (?,?,?,?)");
            mysqli_stmt_bind_param($stAnt, "iiis", $id_atencion, $aid, $valor, $obs);
            mysqli_stmt_execute($stAnt);
        }

        mysqli_commit($conn);
        out(['success'=>true,'id'=>$id_atencion]);
    } catch(Exception $e){
        mysqli_rollback($conn);
        out(['success'=>false,'message'=>$e->getMessage()]);
    }
}

out(['success'=>false,'message'=>'Acción inválida']);
