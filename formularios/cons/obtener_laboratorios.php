<?php
include_once '../../assets/dbc.php';
header('Content-Type: application/json');

$id_cliente = filter_input(INPUT_GET, 'id_cliente', FILTER_VALIDATE_INT);

if (!$id_cliente) {
    echo json_encode([]);
    exit;
}

$query = "
    SELECT acl.id, acl.fecha, acl.resultado, acl.informe, 
           cs.nombre as servicio, acl.id_servicio, acl.id_atencion,
           a.fecha as fecha_atencion
    FROM tbl_atencion_consulta_lab acl
    INNER JOIN cat_servicio cs ON acl.id_servicio = cs.id
    INNER JOIN tbl_atencion a ON acl.id_atencion = a.id
    WHERE a.id_cliente = ?
    ORDER BY acl.fecha DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$laboratorios = [];
while ($row = $result->fetch_assoc()) {
    $laboratorios[] = $row;
}

echo json_encode($laboratorios);
$conn->close();
?>