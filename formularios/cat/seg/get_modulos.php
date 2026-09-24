<?php
require_once '../../../assets/dbc.php';
require_once 'helpers.php';

$sql = "SELECT id, nombre FROM cat_modulo ORDER BY nombre";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = ['id'=>(int)$r['id'],'nombre'=>e($r['nombre'])];
json_response($out);
