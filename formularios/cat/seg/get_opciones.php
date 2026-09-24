<?php
require_once '../../../assets/dbc.php';
require_once 'helpers.php';

$id_mod = isset($_GET['id_modulo']) ? intval($_GET['id_modulo']) : 0;
if (!$id_mod) json_response([],200);

$sql = "SELECT id, nombre FROM cat_modulo_opciones WHERE id_modulo = ? ORDER BY nombre";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i',$id_mod);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = ['id'=>(int)$r['id'],'nombre'=>e($r['nombre'])];
json_response($out);
