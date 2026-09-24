<?php
require_once '../../assets/dbc.php';
$condicion="WHERE a.id_tipo_atencion>1";
if(!isset($_SESSION)) { session_start(); } 
if (isset($_SESSION['cliente_activo']) && !empty($_SESSION['cliente_activo'])) {
	$id_cliente=$_SESSION['cliente_activo'];
	$condicion="WHERE a.id_cliente=$id_cliente";
}
	
$sql = "SELECT 
    a.id, 
    a.numero_ticket, 
    a.fecha, 
    a.fecha_entrega_resultado,
    a.total,
    a.abono,
    a.saldo,
    CONCAT_WS(' ',
        TRIM(b.nombre_1),
        TRIM(b.nombre_2),
        TRIM(b.apellido_1),
        TRIM(b.apellido_2),
        IF(TRIM(b.apellido_casada) IS NULL OR TRIM(b.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(b.apellido_casada)))
    ) AS paciente, 
    a.stat,
    CONCAT_WS(' ',
        TRIM(c.nombre_1),
        TRIM(c.nombre_2),
        TRIM(c.nombre_3),
        TRIM(c.apellido_1),
        TRIM(c.apellido_2),
        IF(TRIM(c.apellido_casada) IS NULL OR TRIM(c.apellido_casada) = '', NULL, CONCAT('de', ' ', TRIM(c.apellido_casada)))
    ) AS medico,
    d.nombre as tiposervicio,
    COALESCE(e.existe, 0) AS existe_enc
FROM tbl_atencion a
LEFT JOIN tbl_persona b ON a.id_cliente = b.id
LEFT JOIN tbl_persona c ON a.id_medico = c.id
LEFT JOIN cat_tipo_atencion d ON a.id_tipo_atencion = d.id
LEFT JOIN (
    SELECT 
        id_atencion, 
        1 AS existe 
    FROM tbl_cuenta_cliente_detalle LIMIT 1
) e ON a.id = e.id_atencion 
		$condicion AND a.id_tipo_atencion<>8 
		ORDER by a.id DESC";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['data' => $data]);
