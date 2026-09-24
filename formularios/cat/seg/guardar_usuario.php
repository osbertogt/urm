<?php
require_once '../../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id_usuario'] ?? 0);
$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$nombres = trim($_POST['nombres'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$id_rol = intval($_POST['id_rol'] ?? 0);
$activo = isset($_POST['activo']) ? 1 : 0;

// Validaciones básicas
if ($nombre_usuario === '' || $nombres === '' || $apellidos === '' || $email === '' || $id_rol <= 0) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
    exit;
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

if ($id === 0 && strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria y debe tener al menos 6 caracteres']);
    exit;
}

try {
    if ($id === 0) {
        // Insertar nuevo usuario
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO cat_usuario (nombre_usuario, nombres, apellidos, email, cadena, hash, id_rol, activo) VALUES (?, ?, ?, ?, '', ?, ?, ?)");
        $stmt->bind_param('sssssis', $nombre_usuario, $nombres, $apellidos, $email, $hash, $id_rol, $activo);
        $stmt->execute();

        if ($stmt->affected_rows === 0) throw new Exception('No se pudo insertar usuario');

    } else {
        // Actualizar usuario
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE cat_usuario SET nombre_usuario=?, nombres=?, apellidos=?, email=?, hash=?, id_rol=?, activo=? WHERE id=?");
            $stmt->bind_param('sssssiii', $nombre_usuario, $nombres, $apellidos, $email, $hash, $id_rol, $activo, $id);
        } else {
            $stmt = $conn->prepare("UPDATE cat_usuario SET nombre_usuario=?, nombres=?, apellidos=?, email=?, id_rol=?, activo=? WHERE id=?");
            $stmt->bind_param('ssssiis', $nombre_usuario, $nombres, $apellidos, $email, $id_rol, $activo, $id);
        }
        $stmt->execute();
        if ($stmt->affected_rows === 0) throw new Exception('No se pudo actualizar usuario');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
