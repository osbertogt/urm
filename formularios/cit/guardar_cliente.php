<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$tipopersona = $id_sexo = 1;

if ($nombre === '') {
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO tbl_persona (id_tipopersona, nombre_1, apellido_1, telefono, email, id_sexo) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $tipopersona, $nombre, $apellido, $telefono, $id_sexo, $email);
$ok = $stmt->execute();
$id = $conn->insert_id;
$stmt->close();

echo json_encode([
  'success' => $ok,
  'id' => $id,
  'nombre' => $nombre
]);
?>
