<?php
require_once '../../assets/dbc.php';

$id = $_GET['id'] ?? null;
// TIMESTAMPDIFF(YEAR, p.fecha_nacimiento, CURDATE()) AS edad,

if ($id) {
    $sql = "
        SELECT 
            a.numero_ticket,
            a.fecha,
            CONCAT_WS(' ',
                TRIM(p.nombre_1), TRIM(p.nombre_2), TRIM(p.nombre_3),
                TRIM(p.apellido_1), TRIM(p.apellido_2),
                IF(TRIM(p.apellido_casada) = '' OR p.apellido_casada IS NULL, '', CONCAT('de ', TRIM(p.apellido_casada)))
            ) AS paciente, 
			a.total,
			a.abono,
			a.saldo
        FROM tbl_atencion a
        JOIN tbl_persona p ON p.id = a.id_cliente
        WHERE a.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    echo json_encode($res);
}
?>

