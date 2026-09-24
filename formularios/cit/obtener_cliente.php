<?php
require_once '../../assets/dbc.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID no proporcionado']);
    exit;
}

$id = (int) $_GET['id'];

$sql = "SELECT 
            id,
            CONCAT(nombre_1, ' ', nombre_2, ' ', apellido_1, ' ', apellido_2) AS nombre_completo,
            telefono,
            email
        FROM tbl_persona
        WHERE id = ? AND id_tipopersona = 1
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Cliente no encontrado']);
}

$stmt->close();
$conn->close();
