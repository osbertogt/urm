<?php
require_once '../../../assets/dbc.php';
require_once 'helpers.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) json_response(['error'=>'id inválido'],400);

$sql = "SELECT id, nombre FROM cat_rol WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if (!$row) json_response(['error'=>'no encontrado'],404);
    json_response(['id'=> (int)$row['id'], 'nombre'=> e($row['nombre'])]);
} else {
    json_response(['error'=>'Query error'],500);
}
