<?php
include_once '../../assets/dbc.php';
$id = intval($_POST['id']);
$sql = "DELETE FROM tbl_persona WHERE id = $id";
mysqli_query($conn, $sql);
echo "ok";
?>
