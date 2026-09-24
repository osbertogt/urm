<?php
require_once '../../../assets/dbc.php';
require_once 'helpers.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$id_rol = isset($data['id_rol']) ? intval($data['id_rol']) : 0;
$id_modulo = isset($data['id_modulo']) ? intval($data['id_modulo']) : 0;
$id_opcion = isset($data['id_opcion']) ? intval($data['id_opcion']) : 0;
$id_permiso = isset($data['id_permiso']) ? intval($data['id_permiso']) : 0;
$csrf = $data['csrf_token'] ?? '';

//if (!$id_rol || !$id_modulo || !$id_opcion || !$id_permiso) json_response(['success'=>false,'error'=>'Campos requeridos'],400);

// prevenir duplicados
$chk = $conn->prepare("SELECT COUNT(*) as cnt FROM tbl_rol_permisos WHERE id_rol = ? AND id_modulo = ? AND id_opcion = ? AND id_permiso = ?");
$chk->bind_param('iiii', $id_rol, $id_modulo, $id_opcion, $id_permiso);
$chk->execute();
$res = $chk->get_result()->fetch_assoc();
if ($res && $res['cnt'] > 0) json_response(['success'=>false,'error'=>'Permiso ya existente'],409);

$ins = $conn->prepare("INSERT INTO tbl_rol_permisos (id_rol, id_modulo, id_opcion, id_permiso) VALUES (?, ?, ?, ?)");
$ins->bind_param('iiii', $id_rol, $id_modulo, $id_opcion, $id_permiso);
if ($ins->execute()) {
    json_response(['success'=>true,'id'=>$conn->insert_id]);
} else {
    json_response(['success'=>false,'error'=>$ins->error],500);
}
