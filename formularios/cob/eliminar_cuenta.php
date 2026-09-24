<?php
include_once '../../assets/dbc.php';
$id = intval($_POST['id']);
$sql = "DELETE FROM tbl_cuenta_cliente WHERE id = $id";
mysqli_query($conn, $sql);
echo "ok";
?>
