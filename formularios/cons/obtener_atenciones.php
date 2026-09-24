<?php
session_start();
include_once '../../assets/dbc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cliente_activo']) || empty($_SESSION['cliente_activo'])) {
    echo json_encode([]);
    exit;
}

$id_cliente = $_SESSION['cliente_activo'];


$query = "
    SELECT ac.id, a.fecha, ac.diagnostico,
           CONCAT(p.nombre_1, ' ', p.nombre_2, ' ', p.apellido_1, ' ', p.apellido_2) as nombre_paciente
    FROM tbl_atencion_consulta ac
    INNER JOIN tbl_atencion a ON ac.id_atencion = a.id
    INNER JOIN tbl_persona p ON a.id_cliente = p.id
    WHERE a.id_tipo_atencion=0 AND a.id_cliente = ?
    ORDER BY a.fecha DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'fecha_atencion' => $row['fecha'],
        'nombre_paciente' => $row['nombre_paciente'],
        'diagnostico' => $row['diagnostico']
    ];
}

echo json_encode($data);
$conn->close();
?>