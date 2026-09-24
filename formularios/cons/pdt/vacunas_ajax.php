<?php

session_start();
require_once '../../../assets/dbc.php'; 
header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';
$cliente = isset($_SESSION['cliente_activo']) ? (int) $_SESSION['cliente_activo'] : 0;
if (!$cliente) {
    echo json_encode(['success'=>false,'message'=>'No hay cliente activo seleccionado']); exit;
}
error_log("=== DEBUG ACTION vacunas===");
error_log("Action value: " . $action);
error_log("REQUEST: " . print_r($_REQUEST, true));
error_log("=====================");

function out($arr){ echo json_encode($arr); exit; }


if ($action === 'list_vacunas') {
    $sql = "SELECT v.id, DATE_FORMAT(v.fecha, '%Y-%m-%d') AS fecha,
                   vac.nombre AS vacuna, d.nombre AS dosis,
                   v.observaciones
            FROM tbl_atencion_consulta_vacunas v
            LEFT JOIN cat_vacuna vac ON vac.id = v.id_vacuna
            LEFT JOIN cat_dosis d ON d.id = v.id_dosis
            WHERE v.id_cliente = ?
            ORDER BY v.fecha DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows); exit;
}

/** Listar cat_vacuna (catalogo) */
if ($action === 'list_catalog_vacunas') {
    $res = $conn->query("SELECT id, nombre FROM cat_vacuna ORDER BY nombre");
    $out = [];
    while($r = $res->fetch_assoc()) $out[] = $r;
    echo json_encode($out); exit;
}

/** Listar cat_dosis (catalogo) */
if ($action === 'list_catalog_dosis') {
    $res = $conn->query("SELECT id, nombre FROM cat_dosis ORDER BY id");
    $out = [];
    while($r = $res->fetch_assoc()) $out[] = $r;
    echo json_encode($out); exit;
}

/** Obtener un registro específico */
if ($action === 'get_vacuna') {
    $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
    if (!$id) out(['success'=>false,'message'=>'ID inválido']);
    $sql = "SELECT id, id_atencion, DATE_FORMAT(fecha, '%Y-%m-%d') AS fecha, id_vacuna, id_dosis, observaciones
            FROM tbl_atencion_consulta_vacunas WHERE id = ?";
    $st = $conn->prepare($sql);
    $st->bind_param("i",$id);
    $st->execute();
    $res = $st->get_result();
    if ($row = $res->fetch_assoc()) out(['success'=>true,'data'=>$row]);
    else out(['success'=>false,'message'=>'No encontrado']);
}

/** Agregar vacuna */
if ($action === 'add_vacuna') {
    $id_vacuna = intval($_POST['vac_id_vacuna'] ?? 0);
    $id_dosis = intval($_POST['vac_id_dosis'] ?? 0);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $observaciones = substr(trim($_POST['vac_observaciones'] ?? ''), 0, 2000);

    if (!$id_vacuna || !$id_dosis) out(['success'=>false,'message'=>'Vacuna o dosis inválida']);

    $conn->begin_transaction();
    try {
        // insertar en tbl_atencion_consulta_vacunas
        $ins = "INSERT INTO tbl_atencion_consulta_vacunas (id_cliente, id_vacuna, id_dosis, fecha, observaciones) VALUES (?, ?, ?, ?, ?)";
        $st4 = $conn->prepare($ins);
        $st4->bind_param("iiiss", $cliente, $id_vacuna, $id_dosis, $fecha, $observaciones);
        $ok = $st4->execute();
        if (!$ok) throw new Exception($conn->error);

        $conn->commit();
        out(['success'=>true]);
    } catch (Exception $e) {
        $conn->rollback();
        out(['success'=>false,'message'=>'Error al guardar: '.$e->getMessage()]);
    }
}

/** Editar vacuna */
if ($action === 'edit_vacuna') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) out(['success'=>false,'message'=>'ID inválido']);
    $id_vacuna = intval($_POST['id_vacuna'] ?? 0);
    $id_dosis = intval($_POST['id_dosis'] ?? 0);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $observaciones = substr(trim($_POST['observaciones'] ?? ''), 0, 2000);

    if (!$id_vacuna || !$id_dosis) out(['success'=>false,'message'=>'Vacuna o dosis inválida']);

    $st = $conn->prepare("UPDATE tbl_atencion_consulta_vacunas SET id_vacuna=?, id_dosis=?, fecha=?, observaciones=? WHERE id=?");
    $st->bind_param("iissi", $id_vacuna, $id_dosis, $fecha, $observaciones, $id);
    $ok = $st->execute();
    out(['success'=>$ok, 'message'=>$ok ? 'Actualizado' : $conn->error]);
}

/** Eliminar vacuna */
if ($action === 'delete_vacuna') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) out(['success'=>false,'message'=>'ID inválido']);
    $st = $conn->prepare("DELETE FROM tbl_atencion_consulta_vacunas WHERE id = ?");
    $st->bind_param("i",$id);
    $ok = $st->execute();
    out(['success'=>$ok, 'message'=>$ok ? 'Eliminado' : $conn->error]);
}

out(['success'=>false,'message'=>'Acción inválida']);
