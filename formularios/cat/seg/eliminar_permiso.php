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

$stmt = $conn->prepare("DELETE FROM tbl_rol_permisos WHERE id = ?");
$stmt->bind_param('i',$id);
if ($stmt->execute()) json_response(['success'=>true]);
else json_response(['success'=>false,'error'=>$stmt->error],500);
