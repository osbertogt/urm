<?php
require '../../assets/dbc.php';
header('Content-Type: application/json');

$id = $_POST['id_analisis'] ?? null;
$nombre = trim($_POST['nombre'] ?? '');
$costo = $_POST['costo'] ?? 0;
$id_tipo = $_POST['id_tipo_atencion'] ?? '';

if (!$nombre || !$id_tipo) {
    //echo json_encode(['status'=>'error','message'=>'Datos obligatorios faltantes']);
    //exit;
}

if ($id) {
    $stmt = $conn->prepare("UPDATE cat_servicio SET nombre=?, id_tipo_atencion=?, costo=? WHERE id=?");
    $stmt->bind_param('sidi', $nombre, $id_tipo, $costo, $id);
} else {
    $stmt = $conn->prepare("INSERT INTO cat_servicio (nombre, id_tipo_atencion, costo) VALUES (?,?,?)");
    $stmt->bind_param('sid', $nombre, $id_tipo, $costo);
}
$ok = $stmt->execute();
echo json_encode(['status'=>$ok ? 'success':'error', 'message'=>$stmt->error]);
