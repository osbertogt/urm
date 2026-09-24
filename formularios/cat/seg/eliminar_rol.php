<?php
require_once '../../../assets/dbc.php';
require_once 'helpers.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = $_POST;

$id = isset($data['id']) ? intval($data['id']) : 0;
$csrf = $data['csrf_token'] ?? '';

if (!check_csrf($csrf)) json_response(['success'=>false,'error'=>'CSRF inválido'],403);
if (!$id) json_response(['success'=>false,'error'=>'id inválido'],400);

$conn->begin_transaction();
try {
    // opcional: eliminar permisos asociados primero
    $stmt = $conn->prepare("DELETE FROM tbl_rol_permisos WHERE id_rol = ?");
    $stmt->bind_param('i',$id);
    if (!$stmt->execute()) throw new Exception($stmt->error);

    $stmt2 = $conn->prepare("DELETE FROM cat_rol WHERE id = ?");
    $stmt2->bind_param('i',$id);
    if (!$stmt2->execute()) throw new Exception($stmt2->error);

    $conn->commit();
    json_response(['success'=>true]);
} catch (Exception $ex) {
    $conn->rollback();
    json_response(['success'=>false,'error'=>$ex->getMessage()],500);
}
