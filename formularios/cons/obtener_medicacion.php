<?php
include_once '../../assets/dbc.php';
header('Content-Type: application/json');

$id_cliente = filter_input(INPUT_GET, 'id_cliente', FILTER_VALIDATE_INT);

if (!$id_cliente) {
    echo json_encode([]);
    exit;
}

$query = "
    SELECT acm.id, acm.fecha, acm.medicamentos, acm.observaciones
	FROM tbl_atencion_consulta_med acm
    WHERE acm.id_cliente = ?
    ORDER BY acm.fecha DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$medicacion = [];
while ($row = $result->fetch_assoc()) {
    $medicacion[] = $row;
}

echo json_encode($medicacion);
$conn->close();
?>