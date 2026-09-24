<?php
require_once "../../../assets/dbc.php";

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$id_cliente = intval($_POST['numerocliente']);

$fecha = $_POST['vac_fecha'] ?? '';
$id_vacuna = intval($_POST['id_vacuna']);
$id_dosis = intval($_POST['id_dosis']);
$observaciones = trim($_POST['vac_observaciones'] ?? '');

// validar cliente → buscamos atencion
$sql_at = "SELECT id FROM tbl_atencion_consulta_vacunas
		   WHERE id_cliente = ? AND id_vacuna = ? AND id_dosis = ? 
		   ";
$stmt_at = mysqli_prepare($conn, $sql_at);
mysqli_stmt_bind_param($stmt_at, "iii", $id_cliente, $id_vacuna, $id_dosis);
mysqli_stmt_execute($stmt_at);
$res_at = mysqli_stmt_get_result($stmt_at);
$atencion = mysqli_fetch_assoc($res_at);
$id_registro = $atencion['id'];

if ($id_registro > 0) {
    $sql = "UPDATE tbl_atencion_consulta_vacunas SET fecha=?, observaciones=? 
	WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $fecha, $observaciones, $id_registro);
    $ok = mysqli_stmt_execute($stmt);
} else {
    $sql = "INSERT INTO tbl_atencion_consulta_vacunas (fecha, id_cliente, id_vacuna, id_dosis, observaciones) VALUES (?,?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "siiis", $fecha, $id_cliente, $id_vacuna, $id_dosis, $observaciones);
    $ok = mysqli_stmt_execute($stmt);
}

echo json_encode($ok ? ["status"=>"success"] : ["status"=>"error","message"=>mysqli_error($conn)]);
