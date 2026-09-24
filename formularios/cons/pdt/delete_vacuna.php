<?php
require_once "../../../assets/dbc.php";

$id = intval($_POST['id']);
$sql = "DELETE FROM tbl_atencion_consulta_vacunas WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
$ok = mysqli_stmt_execute($stmt);

echo json_encode($ok ? ["status"=>"success"] : ["status"=>"error","message"=>mysqli_error($conn)]);
