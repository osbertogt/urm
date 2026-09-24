<?php
require_once '../../../assets/dbc.php';
require_once 'helpers.php';

// Accept either JSON body or form POST (compatibilidad)
$input = null;
$raw = file_get_contents('php://input');
if (!empty($raw)) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $input = $decoded;
}
// fallback to POST
if ($input === null) {
    $input = $_POST;
}

$id = isset($input['id']) ? intval($input['id']) : 0;
$nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
$csrf = $input['csrf_token'] ?? ($input['csrf'] ?? '');

//if (!check_csrf($csrf)) json_response(['success'=>false,'error'=>'CSRF inválido'],403);
if ($nombre === '') json_response(['success'=>false,'error'=>'Nombre requerido'],400);

try {
    if ($id > 0) {
        // update
        $sql = "UPDATE cat_rol SET nombre = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $nombre, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        json_response(['success'=>true,'id'=>$id]);
    } else {
        // insert
        $sql = "INSERT INTO cat_rol (nombre) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $nombre);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        json_response(['success'=>true,'id'=>$conn->insert_id]);
    }
} catch (Exception $ex) {
    json_response(['success'=>false,'error'=>$ex->getMessage()],500);
}
