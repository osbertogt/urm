<?php
require_once "../../../assets/dbc.php";

$id_cliente = intval($_POST['numerocliente']);

$sql = "SELECT v.id, v.fecha, v.observaciones, c.nombre AS vacuna, v.id_vacuna, d.nombre AS dosis
        FROM tbl_atencion_consulta_vacunas v
        JOIN cat_vacuna c ON v.id_vacuna = c.id
		JOIN cat_dosis d ON d.id=v.id_dosis
        WHERE v.id_cliente = ?
        ORDER BY v.fecha DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_cliente);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}
echo json_encode($data);
